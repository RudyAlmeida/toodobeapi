<?php

namespace App\Http\Controllers;

use App\Properties;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PropertiesController extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $per_page = isset($request->per_page) ? $request->per_page : 100;
        return Properties::paginate($per_page)->appends('per_page', $per_page);
    }

    /**
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validatePropertiesRequest());

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        return $this->store($request);
    }

    private function validatePropertiesRequest()
    {
        return [
            'property_value' => 'required|numeric',
            'first_installment' => 'required|numeric',
            'last_installment' => 'required|numeric',
            'income_value' => 'required|numeric'
        ];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     */
    public function store(Request $request)
    {

        if ($this->userCan($request)) {

            if (isset($request->id)) {
                $properties = Properties::updateOrCreate(
                    ['id' => $request->id],
                    $request->all()
                );
            } else {
                $properties = Properties::create($request->all());
            }

            return $properties;
        }
        return response()->json(['error' => 'Você não pode accessar este recurso'], 403);
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

    private function isAdmin()
    {
        $this->setUser();
        return $this->user->role == 'admin';
    }

    private function setUser()
    {
        $this->user = Auth::user();
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $object = Properties::find($id);

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
        $object = Properties::find($id);

        if ($this->userIsOwner($object)) {
            $validator = Validator::make($request->all(),
                array_merge($this->validatePropertiesRequest(),
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
        $object = Properties::find($id);

        if ($this->userIsOwner($object)) {
            if ($object) {
                return $object->delete();
            } else {
                return response()->json(['error' => 'Nada foi encontrado com este ID'], 404);
            }
        }
        return response()->json(['error' => 'Você não possui permissão de acessar este recurso'], 403);
    }
}
