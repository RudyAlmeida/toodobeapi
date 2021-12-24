<?php

namespace App\Observers;

use App\DocumentationRequests;
use App\Http\Controllers\Services\AsaasController;
use App\Invites;
use App\Jobs\ProcessPipeDriveUser;
use App\Projects;
use App\RegistrationForms;
use App\User;
use App\Http\Controllers\Services\PipeDriveService;


class UserObserver
{
    /**
     * Handle the user "created" event.
     *
     * @param User $user
     * @return void
     */
    public function created(User $user)
    {

        if (!$user->referred_code) {
            $user->fill(['referred_code' => $this->generateReferredCode()]);
            $user->save();
        }

        $this->syncToPipeDrive($user);

        $asaas = new AsaasController();
        $asaas->createOrUpdateCustomer($user);
        $this->createRegistrationForm($user);
        $this->deleteInvites($user->email);
        $this->createProject($user);

        if ($user->hasVerifiedEmail()) {
            $this->requestDefaultDocuments($user);
        }


    }

    private function generateReferredCode()
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $referredCode = substr(str_shuffle(str_repeat($pool, 12)), 0, 12);
        if (User::where('referred_code', $referredCode)->first()) {
            $this->generateReferredCode();
        }
        return $referredCode;
    }

    public function createRegistrationForm(User $user)
    {
        $array = [
            'user_id' => $user->id,
            'name' => $user->name,
            'registration_form_type' => 'principal',
            'birthday' => $user->birthday,
            'address_city' => $user->address_city,
            'address_state' => $user->address_state,
            'address_country' => $user->address_country,
            'phone' => $user->mobile
        ];

        RegistrationForms::updateOrCreate($array, $array);

    }

    private function deleteInvites($email)
    {
        Invites::where('email', $email)->delete();
    }

    /**
     * @param User $user
     */
    private function requestDefaultDocuments(User $user)
    {
        if ($user->role != "admin") {
            $default_documents = [
                'RG',
                'CPF',
                'Comprovante de Renda',
                'Comprovante de Residência',
                'Declaração de IR',
                'Certidão de Estado Civil'
            ];

            foreach ($default_documents as $document) {

                $verify = DocumentationRequests::where([
                    'user_id' => $user->id,
                    "document_name" => $document
                ])->first();

                if (!$verify) {
                    $this->createDefaultDocuments($user, $document);
                }
            }
        }
    }

    /**
     * @param User $user
     * @param $document_name
     */
    private function createDefaultDocuments(User $user, $document_name)
    {
        DocumentationRequests::create([
            "user_id" => $user->id,
            'user_name' => $user->name,
            "document_name" => $document_name
        ]);
    }

    /**
     * Handle the user "updated" event.
     *
     * @param User $user
     * @return void
     */
    public
    function updated(User $user)
    {
        if ($user->isDirty()) {
            $this->syncToPipeDrive($user);
            $asaas = new AsaasController();
            $asaas->createOrUpdateCustomer($user);
            $this->createRegistrationForm($user);

            if ($user->hasVerifiedEmail()) {
                $this->requestDefaultDocuments($user);
            }

        }
    }

    /**
     * Handle the user "deleted" event.
     *
     * @param User $user
     * @return void
     */
    public
    function deleted(User $user)
    {
        //
    }

    /**
     * Handle the user "restored" event.
     *
     * @param User $user
     * @return void
     */
    public
    function restored(User $user)
    {
        //
    }

    /**
     * Handle the user "force deleted" event.
     *
     * @param User $user
     * @return void
     */
    public
    function forceDeleted(User $user)
    {
        //
    }


    private function createProject($user)
    {
        if ($user->affiliate_type == 'afiliado') {
            Projects::create([
                'user_id' => $user->id,
                'user_name' => $user->id,
                'project_desciption' => "Projeto Inicial",
                'project_value' => $user->property_value,
                'project_status' => 'em analise'
            ]);
        }

    }

    /**
     * @param $user
     */
    private function syncToPipeDrive($user)
    {
        ProcessPipeDriveUser::dispatch($user);
    }

}
