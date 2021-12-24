<?php

namespace App\Observers;


use App\Jobs\ProcessPipeDriveDeal;
use App\RegistrationForms;


class RegistrationFormsObserver
{
    /**
     * Handle the RegistrationForms "created" event.
     *
     * @param RegistrationForms $registrationForms
     * @return void
     */
    public function created(RegistrationForms $registrationForms)
    {
        $this->createRegistrationFormConjuge($registrationForms);
        $this->syncToPipeDrive($registrationForms);
    }

    /**
     * Handle the RegistrationForms "updated" event.
     *
     * @param RegistrationForms $registrationForms
     * @return void
     */
    public function updated(RegistrationForms $registrationForms)
    {
        if ($registrationForms->isDirty()) {
            $this->createRegistrationFormConjuge($registrationForms);
            $this->syncToPipeDrive($registrationForms);
        }
    }

    /**
     * Handle the RegistrationForms "deleted" event.
     *
     * @param RegistrationForms $registrationForms
     * @return void
     */
    public function deleted(RegistrationForms $registrationForms)
    {
        //
    }

    /**
     * Handle the RegistrationForms "restored" event.
     *
     * @param RegistrationForms $registrationForms
     * @return void
     */
    public function restored(RegistrationForms $registrationForms)
    {
        //
    }

    /**
     * Handle the RegistrationForms "force deleted" event.
     *
     * @param RegistrationForms $registrationForms
     * @return void
     */
    public function forceDeleted(RegistrationForms $registrationForms)
    {
        //
    }

    private function prePopulateRegistrationForm(RegistrationForms $registrationForms)
    {

    }

    public function createRegistrationFormConjuge(RegistrationForms $registrationForms)
    {
        if ($registrationForms->registration_form_type != 'conjuge') {
            if (
                $registrationForms->marital_status == 'casado(a) com. universal de bens' ||
                $registrationForms->marital_status == 'casado(a) com. parcial de bens' ||
                $registrationForms->marital_status == 'casado(a) com. separcao de bens' ||
                $registrationForms->marital_status == 'uniao estavel'
            ) {
                $haveConjugeForm = RegistrationForms::where([
                    'user_id' => $registrationForms->user_id,
                    'registration_form_type' => 'conjuge'
                ])->first();

                    if (!$haveConjugeForm) {
                    $array = [
                        'user_id' => $registrationForms->user_id,
                        'registration_form_type' => 'conjuge'
                    ];

                    RegistrationForms::updateOrCreate($array, $array);
                }

            }
        }
    }

    /**
     * @param $registrationForm
     */
    private function syncToPipeDrive($registrationForm)
    {
        ProcessPipeDriveDeal::dispatch($registrationForm);
    }

}
