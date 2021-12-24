<?php

namespace App\Http\Controllers\Services;

use App\Billings;
use App\Http\Controllers\Controller;
use App\Subscriptions;
use App\User;
use CodePhix\Asaas\Asaas;


class AsaasController extends Controller
{
    /**
     * @var Asaas
     */
    private $asaas;

    /**
     * AsaasController constructor.
     */
    public function __construct()
    {
        $this->asaas = new Asaas(env('ASAAS_TOKEN', '1c337715b822787290b33fadd2b0f7679c159b431e06ac2bf4ec8f519a1d06f9'), env('ASAAS_ENV', 'producao'));
    }

    /**
     * @param User $user
     */
    public function createOrUpdateCustomer(User $user)
    {
        $customer = $this->asaas->cliente->getByEmail($user->email);
        if (!isset($customer->data[0])) {
            $customer = $this->asaas->cliente->create($this->formatCustomer($user));
        } else {
            $customer = $customer->data[0];
            $this->asaas->cliente->update($customer->id, $this->formatCustomer($user));
        }

        if (!$user->payment_gateway_id) {
            $this->paymentGatewayIdSave($user, $customer);
        }
    }

    /**
     * @param User $user
     * @return array
     */
    private function formatCustomer(User $user)
    {
        return [
            "name" => $user->name,
            "email" => $user->email,
            "mobilePhone" => preg_replace('/\D/', '', $user->mobile),
            "cpfCnpj" => preg_replace('/\D/', '', $user->registry_code),
            "externalReference" => $user->id,
            "notificationDisabled" => true
        ];
    }

    /*
     *
     */
    private function paymentGatewayIdSave(User $user, $customer)
    {
        $user->fill(['payment_gateway_id' => $customer->id]);
        $user->save();
    }

    /**
     * @param Subscriptions $subscriptions
     * @return bool|mixed|string
     */
    public function createSubscription(Subscriptions $subscriptions)
    {
        return $this->saveSubscriptionInformation(
            $subscriptions,
            $this->asaas->assinatura->create(
                $this->formatSubscription($subscriptions)
            )
        );
    }

    /**
     * @param Subscriptions $subscriptions
     * @param $assasSubscription
     * @return Subscriptions
     */
    public function saveSubscriptionInformation(Subscriptions $subscriptions, $assasSubscription)
    {
        $array = [
            'payment_gateway_subscription_id' => $assasSubscription->id,
            'user_id' => $subscriptions->user_id,
            'user_name' => $subscriptions->user_name,
            'project_id' => $subscriptions->project_id,
            'payment_gateway_user_id' => $assasSubscription->customer,
            'billing_type' => $assasSubscription->billingType,
            'next_due_date' => $assasSubscription->nextDueDate,
            'value' => $assasSubscription->value,
            'cycle' => $assasSubscription->cycle,
            'description' => $assasSubscription->description,
            'status' => $assasSubscription->status,
        ];

        $subscriptions->fill($array);

        $subscriptions->save();

        return $subscriptions;
    }

    /**
     * @param Subscriptions $subscriptions
     * @return array
     */
    private function formatSubscription(Subscriptions $subscriptions)
    {
        return [
            "customer" => $subscriptions->payment_gateway_customer_id,
            "billingType" => $subscriptions->billing_type,
            "nextDueDate" => $subscriptions->next_due_date,
            "value" => $subscriptions->value,
            "cycle" => $subscriptions->cycle,
            "description" => $subscriptions->description,
            "externalReference" => $subscriptions->id
        ];
    }

    /**
     * @param Subscriptions $subscriptions
     * @return Subscriptions
     */
    public function updateSubscription(Subscriptions $subscriptions)
    {
        return $this->saveSubscriptionInformation(
            $subscriptions,
            $this->asaas->assinatura->update($subscriptions->payment_gateway_subscription_id,
                $this->formatSubscription($subscriptions)
            )
        );
    }

    /**
     * @param $payment_gateway_subscription_id
     * @return bool|mixed|string
     */
    public function getAssasSubscription($payment_gateway_subscription_id)
    {
        return $this->asaas->assinatura->getById($payment_gateway_subscription_id);
    }

    /**
     * @param $payment_gateway_user_id
     * @return bool|mixed|string
     */
    public function getAllAsaasSubscriptionsByUser($payment_gateway_user_id)
    {
        return $this->asaas->assinatura->getByCustomer($payment_gateway_user_id);
    }

    /**
     * @param $payment_gateway_user_id
     * @return bool|mixed|string
     */
    public function getAllAsaasBillsByUser($payment_gateway_user_id)
    {
        return $this->asaas->cobranca->getByCustomer($payment_gateway_user_id);
    }

    /**
     * @param $payment_gateway_billing_id
     * @return bool|mixed|string
     */
    public function getAssasBill($payment_gateway_billing_id)
    {
        return $this->asaas->cobranca->getById($payment_gateway_billing_id);
    }

    /**
     * @param $payment_gateway_subscription_id
     * @return bool|mixed|string
     */
    public function deleteAsaasSubscription($payment_gateway_subscription_id)
    {
        return $this->asaas->assinatura->delete($payment_gateway_subscription_id);
    }

    public function deleteAsaasBill($payment_gateway_billing_id)
    {
        return $this->asaas->cobranca->delete($payment_gateway_billing_id);
    }

    public function updateBill(Billings $billing)
    {
        return $this->saveBillInformation(
            $billing,
            $this->asaas->cobranca->update(
                $billing->payment_gateway_billing_id,
                $this->formatBill($billing)
            )
        );

    }

    /**
     * @param Billings $billing
     * @return Billings
     */
    public function createBill(Billings $billing)
    {
        return $this->saveBillInformation(
            $billing,
            $this->asaas->cobranca->create(
                $this->formatBill($billing)
            )
        );

    }

    /**
     * @param Billings $billing
     * @param $assasBilling
     * @return Billings
     */
    public function saveBillInformation(Billings $billing, $assasBilling)
    {
        $array = [
            'user_id' => $billing->user_id,
            'user_name' => $billing->user_name,
            'payment_gateway_billing_id' => $assasBilling->id,
            'due_date' => $assasBilling->dueDate,
            'original_due_date' => $assasBilling->originalDueDate,
            'client_payment_date' => $assasBilling->clientPaymentDate,
            'payment_gateway_subscription_id' => isset($assasBilling->subscription) ? $assasBilling->subscription : null,
            'value' => $assasBilling->value,
            'billing_type' => $assasBilling->billingType,
            'status' => $assasBilling->status,
            'description' => $assasBilling->description . ' - Invoice Number: #' . $assasBilling->invoiceNumber,
            'invoice_url' => $assasBilling->invoiceUrl,
            'bankslip_url' => isset($assasBilling->bankSlipUrl) ? $assasBilling->bankSlipUrl: null,
            'credit_card' => isset($assasBilling->creditCard) ? $assasBilling->creditCard: null,
        ];

        $billing->fill($array);

        $billing->save();

        return $billing;

    }

    /**
     * @param Billings $billing
     * @return array
     */
    private function formatBill(Billings $billing)
    {
        return [
            "customer" => $billing->payment_gateway_customer_id,
            "billingType" => $billing->billing_type,
            "dueDate" => $billing->due_date,
            "value" => $billing->value,
            "description" => $billing->description,
            "externalReference" => $billing->id,
        ];
    }
}
