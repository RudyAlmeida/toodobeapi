<?php

namespace App\Http\Controllers;

use App\Billings;
use App\Http\Controllers\Services\AsaasController;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BillingsController extends Controller
{
    private $user;

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $per_page = isset($request->per_page) ? $request->per_page : 10;

        if (!$this->isAdmin()) {
            $billings = Billings::where('user_id', $this->user->id)
                ->paginate($per_page)
                ->appends('per_page', $per_page);

        } else {
            $billings = Billings::paginate($per_page)
                ->appends('per_page', $per_page);
        }

        return $billings;
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

    public function indexBySubscription($payment_gateway_subscription_id, Request $request)
    {

        $per_page = isset($request->per_page) ? $request->per_page : 10;

        if (!$this->isAdmin()) {
            $billings = Billings::where(
                [
                    'user_id' => $this->user->id,
                    'payment_gateway_subscription_id' => $payment_gateway_subscription_id
                ]

            )
                ->paginate($per_page)
                ->appends('per_page', $per_page);

        } else {
            $billings = Billings::where('payment_gateway_subscription_id', $payment_gateway_subscription_id)
                ->paginate($per_page)
                ->appends('per_page', $per_page);
        }

        return $billings;
    }

    /**
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function create(Request $request)
    {
        if (!$this->isAdmin()) {
            return response()->json(['error' => 'Apenas Administradores podem criar cobranças'], 400);
        }

        $validator = Validator::make($request->all(), $this->validateBillRequest());

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        return $this->store($request);
    }

    private function validateBillRequest()
    {
        return [
            'user_id' => 'required|integer',
            'description' => 'required|string',
            'value' => 'required|numeric',
            'billing_type' => 'required|in:BOLETO,CREDIT_CARD,UNDEFINED',
            'due_date' => 'required|date'
        ];
    }

    /**
     * @param Request $request
     * @return Billings|JsonResponse
     */
    public function store(Request $request)
    {
        $request->merge(['user_name' => $this->resolveUserName($request->user_id)]);

        if ($this->userCan($request)) {

            $array = $request->all();
            $array['payment_gateway_customer_id'] = $this->getPaymentGatewayCustomerId($request->user_id);

            if (isset($request->id)) {
                $bill = Billings::updateOrCreate(
                    ['user_id' => $request->user_id, 'id' => $request->id],
                    $array
                );

            } else {
                $bill = Billings::create($array);
            }

            return $this->syncAsaas($bill);
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
     * @param $user_id
     * @return mixed
     */
    private function getPaymentGatewayCustomerId($user_id)
    {
        $user = User::find($user_id);
        return $user->payment_gateway_id;
    }

    /**
     * @param Billings $billings
     * @return Billings
     */
    private function syncAsaas(Billings $billings)
    {
        $asaas = new AsaasController();
        if (isset($billings->payment_gateway_billing_id)) {
            return $asaas->updateBill($billings);
        } else {
            return $asaas->createBill($billings);
        }
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $object = Billings::find($id);

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

        if (!$this->isAdmin()) {
            return response()->json(['error' => 'Apenas Administradores podem atualizar cobranças'], 400);
        }

        $validator = Validator::make($request->all(),
            array_merge($this->validateBillRequest(),
                ['id' => 'required|integer']
            ));

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        return $this->store($request);


    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $object = Billings::find($id);

        if ($this->userIsOwner($object)) {
            if ($object) {
                $asaas = new AsaasController();
                $asaas->deleteAsaasBill($object->payment_gateway_billing_id);
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
