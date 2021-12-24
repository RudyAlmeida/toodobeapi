<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Billings extends Model
{
    use SoftDeletes;

    protected $table = 'billing';

    protected $fillable = [
        'user_id',
        'user_name',
        'payment_gateway_billing_id',
        'payment_gateway_subscription_id',
        'due_date',
        'payment_gateway_customer_id',
        'original_due_date',
        'client_payment_date',
        'value',
        'billing_type',
        'status',
        'description',
        'invoice_url',
        'bankslip_url',
        'credit_card'
    ];

    protected $casts = [
        "due_date" => "date",
        "original_due_date" => "date",
        "client_payment_date" => "date",
        "credit_card" => "json"
    ];
}
