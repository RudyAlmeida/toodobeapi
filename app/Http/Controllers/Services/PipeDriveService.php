<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\RegistrationForms;
use App\User;
use Devio\Pipedrive\Pipedrive;
use App\PipeDrive as Model;
use Illuminate\Support\Facades\Log;

class PipeDriveService extends Controller
{
    /**
     * @var Pipedrive
     */
    private $pipeDrive;

    /**
     * PipeDriveService constructor.
     */
    public function __construct()
    {
        $this->pipeDrive = new Pipedrive(
            env('PIPEDRIVE_TOKEN')
        );
    }

    /**
     * @param \Devio\Pipedrive\Http\Response $response
     * @param User $user
     */
    private function savePersonId(\Devio\Pipedrive\Http\Response $response, User $user)
    {
        if ($response->isSuccess()) {
            if (isset(($response->getData())->items[0]->item->id)) {
                $model = Model::updateOrCreate([
                    'user_id' => $user->id
                ], [
                    'person_id' => ($response->getData())->items[0]->item->id,
                    'user_name' => $user->name
                ]);
                Log::info(json_encode($model), ['savePersonId']);
                return $model;
            }

            Log::error(json_encode($response), ['savePersonId']);
            return false;
        }
        Log::error(json_encode($response), ['savePersonId']);
        return false;
    }

    /**
     * @param \Devio\Pipedrive\Http\Response $response
     * @param User $user
     */
    private function saveOrganizationId(\Devio\Pipedrive\Http\Response $response, User $user)
    {
        if ($response->isSuccess()) {
            if (isset(($response->getData())->items[0]->item->id)) {
                $model = Model::updateOrCreate([
                    'user_id' => $user->id
                ], [
                    'organization_id' => ($response->getData())->items[0]->item->id,
                    'user_name' => $user->name
                ]);
                Log::info(json_encode($model), ['saveOrganizationId']);
                return $model;
            }
            Log::error(json_encode($response), ['saveOrganizationId']);
            return false;
        }
        Log::error(json_encode($response), ['saveOrganizationId']);
        return false;
    }

    /**
     * @param \Devio\Pipedrive\Http\Response $response
     * @param RegistrationForms $registrationForms
     * @return bool
     */
    private function saveDealId(\Devio\Pipedrive\Http\Response $response, RegistrationForms $registrationForms)
    {
        if ($response->isSuccess()) {
            if (isset(($response->getData())->items[0]->item->id)) {
                $model = Model::updateOrCreate([
                    'user_id' => $registrationForms->user_id
                ], [
                    'deal_id' => ($response->getData())->items[0]->item->id,
                    'user_name' => $registrationForms->name
                ]);
                Log::info(json_encode($model), ['saveDealId']);
                return $model;
            }
            Log::error(json_encode($response), ['saveDealId']);
            return false;
        }
        Log::error(json_encode($response), ['saveDealId']);
        return false;
    }

    /**
     * @param RegistrationForms $registrationForms
     */
    public function registrationFormFlow(RegistrationForms $registrationForms)
    {
        if ($registrationForms->registration_form_type == 'principal') {
            $pipedriveDatabase = $this->informationExist(User::find($registrationForms->user_id));
            if (isset($pipedriveDatabase->deal_id)) {
//                $this->updateDeal($registrationForms, $pipedriveDatabase->deal_id);
            } elseif (!$this->saveDealId($this->searchDeal($registrationForms), $registrationForms)) {
                $this->saveDealId($this->addDeal($registrationForms), $registrationForms);
            }
        }

        $this->addNoteToDeal($registrationForms);
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function userFlow(User $user)
    {
        $pipedriveDatabase = $this->informationExist($user);

        if (isset($pipedriveDatabase->organization_id)) {
            $this->updateOrganization($user, $pipedriveDatabase->organization_id);
        } elseif (!$this->saveOrganizationId($this->searchOrganization($user), $user)) {
            $this->saveOrganizationId($this->addOrganization($user), $user);
        }

        if (isset($pipedriveDatabase->person_id)) {
            $this->updatePerson($user, $pipedriveDatabase->person_id);
        } elseif (!$this->savePersonId($this->searchPerson($user), $user)) {
            $this->savePersonId($this->addPerson($user), $user);
        }

        return $this->informationExist($user);
    }


    /**
     * @param User $user
     * @return \Devio\Pipedrive\Http\Response
     */
    public function addPerson(User $user)
    {
        $return = $this->pipeDrive->persons()->add(
            $this->formatPersonFromUser($user)
        );

        Log::info(json_encode($return), ['addPerson']);
        return $return;
    }

    /**
     * @param User $user
     * @return mixed
     */
    private function informationExist(User $user)
    {
        return Model::where('user_id', $user->id)->first();
    }

    /**
     * @param User $user
     * @return \Devio\Pipedrive\Http\Response
     */
    public function addOrganization(User $user)
    {
        $return = $this->pipeDrive->organizations()->add(
            $this->formatOrganizationFromUser($user)
        );

        Log::info(json_encode($return), ['addOrganization']);
        return $return;

    }

    /**
     * @param User $user
     * @param $organizationId
     * @return \Devio\Pipedrive\Http\Response
     */
    public function updateOrganization(User $user, $organizationId)
    {
        $return = $this->pipeDrive->organizations()->update(
            $organizationId,
            $this->formatOrganizationFromUser($user)
        );

        Log::info(json_encode($return), ['updateOrganization']);
        return $return;

    }

    public function addNoteToDeal(RegistrationForms $registrationForms)
    {
        $pipedriveDatabase = $this->informationExist(User::find($registrationForms->user_id));
        if (isset($pipedriveDatabase->deal_id)) {
            $dealId = $pipedriveDatabase->deal_id;
            $return = $this->pipeDrive->notes()->add([
                'deal_id' => $dealId,
                'content' => 'Ficha atualizada: (' . $registrationForms->registration_form_type . ') ' . env('APP_URL') . '/ficha/' . $registrationForms->id
            ]);
            Log::info(json_encode($return), ['addNoteToDeal']);
        }
    }


    /**
     * @param User $user
     * @param $pipeDrivePersonId
     * @return \Devio\Pipedrive\Http\Response
     */
    public function updatePerson(User $user, $pipeDrivePersonId)
    {
        $return = $this->pipeDrive->persons()->update(
            $pipeDrivePersonId,
            $this->formatPersonFromUser($user)
        );

        Log::info(json_encode($return), ['updatePerson']);

        return $return;

    }

    /**
     * @param User $user
     * @return \Devio\Pipedrive\Http\Response
     */
    public function searchPerson(User $user)
    {
        $return = $this->pipeDrive->persons()->search($user->email);
        Log::info(json_encode($return), ['searchPerson']);
        return $return;
    }

    /**
     * @param User $user
     * @return \Devio\Pipedrive\Http\Response
     */
    public function searchOrganization(User $user)
    {
        $return = $this->pipeDrive->organizations()->search($user->name);
        Log::info(json_encode($return), ['searchOrganization']);
        return $return;
    }

    /**
     * @param $name
     * @return array
     */
    private function splitName($name)
    {
        $name = trim($name);
        $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
        $first_name = trim(preg_replace('#' . $last_name . '#', '', $name));
        return array($first_name, $last_name);
    }

    /**
     * @param User $user
     * @return array
     */
    private function formatPersonFromUser(User $user)
    {
        return [
            'name' => $user->name,
            'first_name' => isset(($this->splitName($user->name))[0]) ? ($this->splitName($user->name))[0] : null,
            'last_name' => isset(($this->splitName($user->name))[1]) ? ($this->splitName($user->name))[1] : null,
            'b4b54fa266683b893493641620d29143ee4bf3cd' => $user->registry_code // CPF
        ];
    }

    /**
     * @param User $user
     * @return array
     */
    private function formatOrganizationFromUser(User $user)
    {
        return [
            "name" => $user->name,
            "address" => "",
            "address_street_number" => "",
            "address_route" => "",
            "address_sublocality" => "",
            "address_admin_area_level_1" => $user->address_state,
            "address_admin_area_level_2" => $user->address_city,
            "address_country" => $user->address_country
        ];
    }

    /**
     * @param $registrationForms
     * @return array
     */
    private function formatDeal(RegistrationForms $registrationForms)
    {
        $user = User::find($registrationForms->user_id);
        $parceiro = User::where('referred_code', $user->affiliate_code)->first();

        if (!$parceiro) {
            $parceiro = $user;
        }

        $pipedriveInfo = Model::where('user_id', $user->id)->first();

        return [
            "title" => $user->name,
            "value" => $user->property_value,
            "currency" => "BRL",
            "person_id" => $pipedriveInfo->person_id,
            "org_id" => $pipedriveInfo->organization_id,
            "b15c2c7c580cc6b0aa3779206e9b37144cde5d19" => $parceiro->name . '_' . $parceiro->referred_code, // Parceiro
            "50660b67882b4f2b07f962a029226a4ec8a11d7d" => $parceiro->mobile, // Telefone do parceiro
            "a6408c6f05bfd016bce68a700ddf2c7753375fd2" => $user->mobile, //Telefone do Cliente
            "2aa928a71d5fb711d1ab0af5f5fb96285e01b6c7" => $parceiro->name . '_' . $parceiro->referred_code, // Indicação
            "60286b9919bf9521d7cb06c061ee90ec3c5d2ed0" => "", //RG
            "8faff0168277ec75b04ceb687a5dfc293962764a" => $user->registry_code, //CPF
            "9e74396b90ec9e7102a953e008511b65587c45a4" => $registrationForms->fgts_value, //FGTS
            "9e74396b90ec9e7102a953e008511b65587c45a4_currency" => "BRL", //Moeda de FGTS
            "dd5ea80f14add68dbc04fe7156d0a8857810b7a6" => $registrationForms->profession, //Profissão
            "6b1754357b814c0c720787448b28ade656148d8a" => $registrationForms->company_admission_date, // Admissão
            "820c682509824b02e49071cf579671cdc18f303f" => $registrationForms->marital_status, //Estado Civil
        ];
    }


    /**
     * @param RegistrationForms $registrationForms
     * @return \Devio\Pipedrive\Http\Response
     */
    public function searchDeal(RegistrationForms $registrationForms)
    {
        $return = $this->pipeDrive->deals()->search($registrationForms->name);
        Log::info(json_encode($return), ['searchDeal']);
        return $return;
    }

    /**
     * @param RegistrationForms $registrationForms
     * @return \Devio\Pipedrive\Http\Response
     */
    public function addDeal(RegistrationForms $registrationForms)
    {
        $return = $this->pipeDrive->deals()->add(
            array_merge(["stage_id" => 27], $this->formatDeal($registrationForms))
        );
        Log::info(json_encode($return), ['addDeal']);
        return $return;
    }

    /**
     * @param RegistrationForms $registrationForms
     * @param $pipedriveDealId
     * @return \Devio\Pipedrive\Http\Response
     */
    public function updateDeal(RegistrationForms $registrationForms, $pipedriveDealId)
    {
        $return = $this->pipeDrive->deals()->update($pipedriveDealId,
            $this->formatDeal($registrationForms)
        );
        Log::info(json_encode($return), ['updateDeal']);
        return $return;
    }
}
