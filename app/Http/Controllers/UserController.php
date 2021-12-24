<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Services\GoogleDrive;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Image;


class UserController extends Controller
{

    /**
     * @var
     */
    private $user;

    public function __construct()
    {

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|JsonResponse
     */
    public function index(Request $request)
    {
        if (!$this->isAdmin()) {
            return response()->json(['error' => 'Você não possui permissão de acessar este recurso'], 403);
        }

        $per_page = isset($request->per_page) ? $request->per_page : 10;

        $user = User::query()
            ->when($request->get('search'), function ($query) use ($request) {
                $query->where('name', 'LIKE', "%{$request->get('search')}%");
            })
            ->paginate($per_page)
            ->appends('per_page', $per_page);


        if ($request->get('search')) {
            $user->appends('search', $request->get('search'));
        }

        return $user;

    }

    /**
     * @return bool
     */
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
        if (!$this->isAdmin()) {
            return response()->json(['error' => 'Você não possui permissão de acessar este recurso'], 403);
        }

        $validator = Validator::make($request->all(), array_merge($this->validateUser(), [
            'email' => 'required|email|unique:users',
            'registry_code' => 'required|string|unique:users|cpf|formato_cpf'
        ]));

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        if ($request->hasFile('user_image')) {
            $photo = $request->file('user_image');
            $extension = $photo->getClientOriginalExtension();
            $photo = Image::make($photo)->resize(300, 300);
            $request->offsetUnset('user_image');
            $googleDrive = new GoogleDrive();
            $request->merge(['photo' => $googleDrive->uploadUserPhoto(
                $photo->stream($extension, 60),
                $request->registry_code . '-' . Str::of($request->name)->slug('-') . '.' . $extension)
            ]);
        }

        return $this->store($request);

    }

    private function validateUser()
    {
        return [
            'name' => 'required|string',
            'affiliate_code' => 'sometimes|string',
            'mobile' => 'required|string|celular_com_ddd',
            'birthday' => 'required|date',
            'password' => 'sometimes|string',
            'address_city' => 'required|string',
            'address_state' => 'required|string',
            'address_country' => 'required|string',
            'user_image' => 'sometimes|file',
        ];
    }

    private function validateUserUpdate()
    {
        return [
            'name' => 'sometimes|string',
            'affiliate_code' => 'sometimes|string',
            'mobile' => 'sometimes|string|celular_com_ddd',
            'birthday' => 'sometimes|date',
            'password' => 'sometimes|string',
            'address_city' => 'sometimes|string',
            'address_state' => 'sometimes|string',
            'address_country' => 'sometimes|string',
            'user_image' => 'sometimes|file',
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        return User::updateOrCreate(
            ['id' => $request->id],
            $request->all()
        );
    }

    /**
     * @param $request
     * @return bool
     */


    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['error' => 'Você não possui permissão de acessar este recurso'], 403);
        }

        $object = User::find($id);

        if ($object) {
            return $object;
        } else {
            return response()->json(['error' => 'Nada foi encontrado com este ID'], 404);
        }

    }


    /**
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function update(Request $request, $id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['error' => 'Você não possui permissão de acessar este recurso'], 403);
        }

        $validator = Validator::make($request->all(),
            array_merge($this->validateUserUpdate(),
                ['id' => 'required|integer']
            ));

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        if ($request->hasFile('user_image')) {
            $photo = $request->file('user_image');
            $extension = $photo->getClientOriginalExtension();
            $photo = Image::make($photo)->resize(300, 300);
            $request->offsetUnset('user_image');
            $googleDrive = new GoogleDrive();
            $request->merge(['photo' => $googleDrive->uploadUserPhoto(
                $photo->stream($extension, 60),
                $request->registry_code . '-' . Str::of($request->name)->slug('-') . '.' . $extension)
            ]);
        }
        return $this->store($request);

    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $object = User::find($id);

        if ($object) {
            return $object->delete();
        } else {
            return response()->json(['error' => 'Nada foi encontrado com este ID'], 404);
        }
    }

}
