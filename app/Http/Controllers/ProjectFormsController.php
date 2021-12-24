<?php

namespace App\Http\Controllers;

use App\Projects;
use App\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\RegistrationForms;


class ProjectFormsController extends Controller
{

    private $user;

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $per_page = isset($request->per_page) ? $request->per_page : 10;
        $search = isset($request->search) ? $request->search : '';

        if (!$this->isAdmin()) {
            $projectForms = Projects::where('user_id', $this->user->id)
                ->where('project_desciption', 'like', $search . '%')
                ->paginate($per_page)
                ->appends('per_page', $per_page)
                ->appends('search', $search);

        } else {
            $projectForms = Projects::where('project_desciption', 'like', '%' . $search . '%')
                ->paginate($per_page)
                ->appends('per_page', $per_page)
                ->appends('search', $search);
        }

        return $projectForms;
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
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validateProjectFormsRequest());

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        return $this->store($request);
    }

    private function validateProjectFormsRequest()
    {
        return [
            'user_id' => 'integer',
            'project_desciption' => 'required|string',
            'project_value' => 'required|numeric',
            'project_status' => 'sometimes|string',
            'property_type' => 'sometimes|array',
            'bedrooms' => 'sometimes|integer',
            'parking_spaces' => 'sometimes|integer',
            'leisure_sport' => 'sometimes|array',
            'amenities_services' => 'sometimes|array',
            'safety' => 'sometimes|array',
            'rooms' => 'sometimes|array',
        ];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     */
    public function store(Request $request)
    {

        $request->merge(['user_name' => $this->resolveUserName($request->user_id)]);

        if ($this->userCan($request)) {

            if (isset($request->id)) {
                $project =  Projects::updateOrCreate(
                    ['user_id' => $request->user_id, 'id' => $request->id],
                    $request->all()
                );
            } else {
                $project = Projects::create($request->all());
            }

            $subscription = new SubscriptionsController();
            return $subscription->createFromProject($project);
        }
        return response()->json(['error' => 'Você não pode criar este recurso'], 403);
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
        $object = Projects::find($id);

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
        $object = Projects::find($id);

        if ($this->userIsOwner($object)) {
            $validator = Validator::make($request->all(),
                array_merge($this->validateProjectFormsRequest(),
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
        $object = Projects::find($id);

        if ($this->userIsOwner($object)) {
            if ($object) {
                return $object->delete();
            } else {
                return response()->json(['error' => 'Nada foi encontrado com este ID'], 404);
            }
        }
        return response()->json(['error' => 'Você não possui permissão de acessar este recurso'], 403);
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
