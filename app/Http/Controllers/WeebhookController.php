<?php

namespace App\Http\Controllers;

use App\Subscriptions;
use App\User;
use App\Billings;
use Illuminate\Http\Request;
use App\Http\Controllers\Services\AsaasController;
use Mockery\Exception;

class WeebhookController extends Controller
{

    private $user;
    public function webhookHandle(Request $request)
    {
        try {
            $this->user = $this->customerResolver($request->payment['customer']);

            if(!$this->user){
                return response()->json('user not found, but ok. Next!', 200);
            }

            switch ($request->event) {
                case "PAYMENT_CREATED":
                    return $this->billResolver($request->payment);
                    break;
                case "PAYMENT_UPDATED":
                    return $this->billResolver($request->payment);
                    break;
                case "PAYMENT_CONFIRMED":
                    return $this->billResolver($request->payment);
                    break;
                case "PAYMENT_RECEIVED":
                    return $this->billResolver($request->payment);
                    break;
                case "PAYMENT_OVERDUE":
                    return $this->billResolver($request->payment);
                    break;
                case "PAYMENT_DELETED":
                    return $this->billingDelete($request->payment);
                    break;
                case "PAYMENT_RESTORED":
                    return $this->billResolver($request->payment);
                    break;
                case "PAYMENT_REFUNDED":
                    return $this->billResolver($request->payment);
                    break;
                case "PAYMENT_RECEIVED_IN_CASH_UNDONE":
                    return $this->billResolver($request->payment);
                    break;
                case "PAYMENT_CHARGEBACK_REQUESTED":
                    return $this->billResolver($request->payment);
                    break;
                case "PAYMENT_CHARGEBACK_DISPUTE":
                    return $this->billResolver($request->payment);
                    break;
                case "PAYMENT_AWAITING_CHARGEBACK_REVERSAL":
                    return $this->billResolver($request->payment);
                    break;
            }
        }catch (Exception $exception){
            return response()->json($exception->getMessage(), 200);
        }
    }

    /**
     * @param $customer
     * @return mixed
     */
    private function customerResolver($customer)
    {
        return User::where('payment_gateway_id', $customer)->first();
    }

    /**
     * @param $payment
     * @return Billings
     */
    private function billResolver($payment)
    {
        if(isset($payment['subscription'])){
            $this->updateSubscription($payment['subscription']);
        }

        $model = Billings::where('payment_gateway_billing_id', $payment['id'])->first();
        if($model){
           return $this->updateBilling($model, $payment);
        }else{
            return $this->createBilling($payment);
        }
    }

    private function updateBilling($model, $payment)
    {
        $asaas = new AsaasController();
        return $asaas->saveBillInformation($model, (object) $payment);
      }

    private function createBilling($payment)
    {
        $billing = Billings::create(
            [
                'user_id' => $this->user->id,
                'user_name' => $this->user->name,
                'payment_gateway_billing_id' => isset($payment['id']) ? $payment['id'] : null,
                'due_date'=> isset($payment['dueDate']) ? $payment['dueDate'] : null,
                'payment_gateway_subscription_id' => isset($payment['subscription']) ? $payment['subscription'] : null,
                'payment_gateway_customer_id' => isset($payment['customer']) ? $payment['customer']: null,
                'original_due_date' => isset($payment['originalDueDate']) ? $payment['originalDueDate']: null,
                'client_payment_date' => isset($payment['clientPaymentDate']) ? $payment['clientPaymentDate']: null,
                'value' => isset($payment['value']) ? $payment['value']: null,
                'billing_type'=> isset($payment['billingType']) ? $payment['billingType']: null,
                'status'=> isset($payment['status']) ? $payment['status']: null,
                'description'=> isset($payment['description']) ? $payment['description']: null,
                'invoice_url'=> isset($payment['invoiceUrl']) ? $payment['invoiceUrl']: null,
                'bankslip_url'=> isset($payment['bankSlipUrl']) ? $payment['bankSlipUrl']: null,
                'credit_card'=> isset($payment['creditCard']) ? $payment['creditCard']: null
            ]
        );
        return $billing;
    }

    /**
     * @param $payment
     */
    private function billingDelete($payment)
    {
        if(isset($payment['subscription'])){
            $this->updateSubscription($payment['subscription']);
        }

        $billing = Billings::where('payment_gateway_billing_id', $payment['id'])->first();

        if($billing){
           $billing->delete();
        }

    }

    private function updateSubscription($payment_gateway_subscription_id)
    {
        $asaas = new AsaasController();

        $model = Subscriptions::where('payment_gateway_subscription_id', $payment_gateway_subscription_id)->first();

        $assasSubscription = $asaas->getAssasSubscription($payment_gateway_subscription_id);

        if($model){
            return $asaas->saveSubscriptionInformation($model, $assasSubscription);
        }else{
            return Subscriptions::create(
                [
                    'payment_gateway_subscription_id' => $assasSubscription->id,
                    'user_id' => $this->user->id,
                    'user_name' => $this->user->name,
                    'payment_gateway_customer_id' => $assasSubscription->customer,
                    'payment_gateway_user_id' => $assasSubscription->customer,
                    'billing_type' => $assasSubscription->billingType,
                    'next_due_date' => $assasSubscription->nextDueDate,
                    'value' => $assasSubscription->value,
                    'cycle' => $assasSubscription->cycle,
                    'description' => $assasSubscription->description,
                    'status' => $assasSubscription->status,
                ]
            );
        }
    }


}
