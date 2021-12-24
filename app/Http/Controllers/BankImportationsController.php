<?php

namespace App\Http\Controllers;

use App\BankInformations;
use App\Projects;
use App\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\RegistrationForms;


class BankImportationsController extends Controller
{

    private $user;

    /**
     * @return mixed
     * @throws Exception
     */
    public function index()
    {
        $per_page = isset($request->per_page) ? $request->per_page : 10;
        $search = isset($request->search) ? $request->search : '';

        if (!$this->isAdmin()) {
            $bankInformation = BankInformations::where('user_id', $this->user->id)
                ->where('account_name', 'like', $search . '%')
                ->paginate($per_page)
                ->appends('per_page',$per_page)
                ->appends('search',$search);

        } else {
            $bankInformation = BankInformations::where('account_name', 'like', '%' . $search . '%')
                ->paginate($per_page)
                ->appends('per_page',$per_page)
                ->appends('search',$search);
        }

        return $bankInformation;

    }

    private function isAdmin()
    {
        $this->setUser();
        return $this->user->role == 'admin' ? true : false;
    }

    private function setUser()
    {
        $this->user = Auth::user();
    }

    /**
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validateBankInformationRequest());

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        return $this->store($request);
    }

    private function validateBankInformationRequest()
    {
        return [
            'user_id' => 'required|integer',
            'for_commissions' => 'boolean',
            'account_name' => 'string',
            'bank_account_type' => 'in:CONTA_CORRENTE,CONTA_POUPANCA',
            'owner_name' => 'string',
            'owner_birth_date' => 'date',
            'registry_code' => 'required',
            'bank_code' => 'string',
            'agency' => 'string',
            'account' => 'string',
            'account_digit' => 'string',
        ];
    }

    private function keepOneToCommissions($request)
    {
        if(
            $request->bank_information_type == 'bank' &&
            $request->for_commissions
        ){
            $forCommissions = BankInformations::where([
                    'bank_information_type' =>'bank',
                    'user_id' => $request->user_id,
                    'for_commissions' => true
                ])->first();

            if($forCommissions){
                $forCommissions->for_commissions = false;
                $forCommissions->save();
            }
        }


    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     */
    public function store(Request $request)
    {
        $request->merge(['user_name' => $this->resolveUserName($request->user_id)]);

        $this->keepOneToCommissions($request);

        if ($this->userCan($request)) {
            if (isset($request->id)) {
                return BankInformations::updateOrCreate(
                    ['user_id' => $request->user_id, 'id' => $request->id],
                    $request->all()
                );
            } else {
                return BankInformations::create($request->all());
            }
        } else {
            return response()->json(['error' => 'A informação bancária não pertence a este usuário'], 403);
        }

    }

    private function userCan($request)
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($request->user_id == $this->user->id) {
            return true;
        }

        return false;
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $object = BankInformations::find($id);

        if ($this->userIsOwner($object)) {
            if ($object) {
                return $object;
            } else {
                return response()->json(['error' => 'Nada foi encontrado com este ID'], 404);
            }

        }
        return response()->json(['error' => 'Você não possui permissão de acessar este recurso'], 403);

    }

    private function userIsOwner($object)
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($object->user_id == $this->user->id) {
            return true;
        }

        return false;
    }

    /**
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function update(Request $request, $id)
    {
        $object = BankInformations::find($id);

        if ($this->userIsOwner($object)) {
            $validator = Validator::make($request->all(),
                array_merge($this->validateBankInformationRequest(),
                    ['id' => 'required|integer']
                ));

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            return $this->store($request);

        } else {
            return response()->json(['error' => 'Você não possui permissão de acessar este recurso'], 403);
        }
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $object = BankInformations::find($id);

        if ($this->userIsOwner($object)) {
            if ($object) {
                return $object->delete();
            } else {
                return response()->json(['error' => 'Nada foi encontrado com este ID'], 404);
            }
        }
        return response()->json(['error' => 'Você não possui permissão de acessar este recurso'], 403);
    }

    private function checkRegisterForm($request)
    {
        $registration = RegistrationForms::where([
            'user_id' => $request->user_id,
            'registration_form_type' => 'principal'
        ])->first();

        if ($registration->id == $request->registration_form_id) {
            return true;
        }

        return false;

    }

    /**
     * @param $user_id
     * @return mixed
     */
    private function resolveUserName($user_id)
    {
        return (User::find($user_id))->name;
    }



}
