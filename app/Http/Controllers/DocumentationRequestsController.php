<?php

namespace App\Http\Controllers;

use App\DocumentationRequests;
use App\Http\Controllers\Services\GoogleDrive;
use App\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class DocumentationRequestsController extends Controller
{
    /**
     * @var
     */
    private $user;

    /**
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function index(Request $request)
    {
        $per_page = isset($request->per_page) ? $request->per_page : 10;

        if (!$this->isAdmin()) {
            $documentationRequest = DocumentationRequests::where('user_id', $this->user->id)
                ->when($request->get('search'), function ($query) use ($request) {
                    $query->where('document_name', 'LIKE', "%{$request->get('search')}%");
                })
                ->paginate($per_page)
                ->appends('per_page', $per_page);

        } else {
            $documentationRequest = DocumentationRequests::query()
                ->when($request->get('search'), function ($query) use ($request) {
                    $query->where('user_name', 'LIKE', "%{$request->get('search')}%")
                    ->orWhere('document_status', $request->get('search'));
                })
                ->paginate($per_page)
                ->appends('per_page', $per_page);
        }

        if ($request->get('search')) {
            $documentationRequest->appends('search', $request->get('search'));
        }

        return $documentationRequest;

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
        if ($this->isAdmin()) {
            $validator = Validator::make($request->all(), $this->validateDocumentationRequests());

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            return $this->store($request);

        } else {
            return response()->json(['error' => 'Somente administradores podem criar este recurso'], 403);
        }
    }

    private function validateDocumentationRequests()
    {
        return [
            "user_id" => 'required|integer',
            "document_name" => 'required|string',
            "user_file" => 'sometimes|file',
            "document_status" => 'sometimes|in:aguardando,enviado,aprovado,recusado',
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
        $request->merge(['user_name' => $this->resolveUserName($request->user_id)]);

        if($request->has('document_file')){
            $request->merge(['document_status' => 'enviado']);
        }

        if ($this->userCan($request)) {
            return DocumentationRequests::updateOrCreate(
                ['user_id' => $request->user_id, 'document_name' => $request->document_name],
                $request->all()
            );
        }
    }

    /**
     * @param $request
     * @return bool
     */
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
        $object = DocumentationRequests::find($id);

        if ($this->userIsOwner($object)) {
            if ($object) {
                return $object;
            } else {
                return response()->json(['error' => 'Nada foi encontrado com este ID'], 404);
            }

        }
        return response()->json(['error' => 'Você não possui permissão de acessar este recurso'], 403);

    }

    /**
     * @param $object
     * @return bool
     */
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
        $object = DocumentationRequests::find($id);

        if ($this->userIsOwner($object)) {
            $validator = Validator::make($request->all(),
                array_merge($this->validateDocumentationRequests(),
                    ['id' => 'required|integer']
                ));

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }


            if ($request->hasFile('user_file')) {

                $file = $request->file('user_file');
                $extension = $file->getClientOriginalExtension();
                $request->offsetUnset('user_file');

                $googleDrive = new GoogleDrive();

                $request->merge(['document_file' => $googleDrive->uploadDocumentFile(
                    $this->user->registry_code,
                    file_get_contents($file),
                     Str::of($request->document_name)->slug('-') . '.' . $extension
                )]);
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
        $object = DocumentationRequests::find($id);

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
