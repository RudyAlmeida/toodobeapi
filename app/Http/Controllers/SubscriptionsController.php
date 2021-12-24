<?php

namespace App\Http\Controllers;

use App\Billings;
use App\Http\Controllers\Services\AsaasController;
use App\Projects;
use App\Subscriptions;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SubscriptionsController extends Controller
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
            $subscriptions = Subscriptions::where('user_id', $this->user->id)
                ->paginate($per_page)
                ->appends('per_page', $per_page)
                ->appends('search', $search);

        } else {
            $subscriptions = Subscriptions::paginate($per_page)
                ->appends('per_page', $per_page)
                ->appends('search', $search);
        }

        return $subscriptions;
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
        if (!$this->isAdmin()) {
            return response()->json(['error' => 'Apenas Administradores podem criar assinaturas'], 400);
        }

        $validator = Validator::make($request->all(), $this->validateSubscriptionsRequest());

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        return $this->store($request);
    }

    private function validateSubscriptionsRequest()
    {
        return [
            'user_id' => 'required|integer',
            'description' => 'required|string',
            'value' => 'required|numeric',
            'status' => 'sometimes|in:ACTIVE,EXPIRED',
            'cycle' => 'sometimes|in:WEEKLY,BIWEEKLY,MONTHLY,QUARTERLY,SEMIANNUALLY,YEARLY',
            'billing_type' => 'required|in:BOLETO,CREDIT_CARD,UNDEFINED',
            'next_due_date' => 'sometimes|date'
        ];
    }


    /**
     * @param $user_id
     * @return mixed
     */
    private function resolveUserName($user_id)
    {
        return (User::find($user_id))->name;
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

            $array = $request->all();
            $array['payment_gateway_customer_id'] = $this->getPaymentGatewayCustomerId($request->user_id);

            if (!isset($request->cycle)) {
                $array['cycle'] = "MONTHLY";
            }

            if (isset($request->id)) {
                $subscription = Subscriptions::updateOrCreate(
                    ['user_id' => $request->user_id, 'id' => $request->id],
                    $array
                );
            } else {
                $subscription = Subscriptions::create($array);
            }

            return $this->syncAsaas($subscription);
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

    private function syncAsaas(Subscriptions $subscriptions)
    {
        $asaas = new AsaasController();
        if (isset($subscriptions->payment_gateway_subscription_id)) {
            return $asaas->updateSubscription($subscriptions);
        } else {
            return $asaas->createSubscription($subscriptions);
        }
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $object = Subscriptions::find($id);

        if ($this->userIsOwner($object)) {
            if ($object) {
                $subscription = $object;
                $subscription->billings = Billings::where(
                    'payment_gateway_subscription_id',
                    $object->payment_gateway_subscription_id
                )->get();
                return $subscription;

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
            return response()->json(['error' => 'Apenas Administradores podem atualizar assinaturas'], 400);
        }

        $validator = Validator::make($request->all(),
            array_merge($this->validateSubscriptionsRequest(),
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
        $object = Subscriptions::find($id);

        if ($this->userIsOwner($object)) {
            if ($object) {
                $asaas = new AsaasController();
                $asaas->deleteAsaasSubscription($object->payment_gateway_subscription_id);
                return $object->delete();
            } else {
                return response()->json(['error' => 'Nada foi encontrado com este ID'], 404);
            }
        }
        return response()->json(['error' => 'Você não possui permissão de acessar este recurso'], 403);
    }

    public function createFromProject(Projects $project)
    {
        $request = new Request();

        $request->replace([
            'project_id' => $project->id,
            'user_id' => $project->user_id,
            'description' => 'Assinatura referente ao projeto #' . $project->id . '. Descrição: ' . $project->project_desciption,
            'value' => $this->fromProjectSubscriptionValue(),
            'cycle' => 'MONTHLY',
            'billing_type' => 'UNDEFINED',
            'next_due_date' => $this->fromProjectNextDuaDate()
        ]);

        $project->subscription = $this->store($request);
        $project->subscription->url = env('FRONTEND_URL') . '/#/detalhes-assinatura/' . $project->subscription->id;
        return $project;

    }

    private function fromProjectSubscriptionValue()
    {
        return 1000;
    }

    public function fromProjectNextDuaDate()
    {
        $today = Carbon::now();
        $month = Carbon::now()->format('m');
        $year = Carbon::now()->format('Y');
        $theFifteenthDayOfThisMonth = $year . '-' . $month . '-15';
        $theFifteenthDayOfThisMonth = new Carbon($theFifteenthDayOfThisMonth);


        if ($today >= $theFifteenthDayOfThisMonth) {
            $month = Carbon::now()->addMonth(1)->format('m');
            $year = Carbon::now()->format('Y');
            $fistDayOfNextMonth =  $year . '-' . $month . '-1';
            $fistDayOfNextMonth =new Carbon($fistDayOfNextMonth);

            return $fistDayOfNextMonth->format('Y-m-d');
        } else {

            return $theFifteenthDayOfThisMonth->format('Y-m-d');
        }
    }
}
