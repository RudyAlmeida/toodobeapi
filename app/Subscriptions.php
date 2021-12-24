<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscriptions extends Model
{
    use SoftDeletes;

    protected $table = 'subscription';

    protected $fillable = [
        'payment_gateway_subscription_id',
        'user_id',
        'user_name',
        'project_id',
        'payment_gateway_customer_id',
        'billing_type',
        'next_due_date',
        'value',
        'cycle',
        'description',
        'status',
        'credit_card'
    ];

    protected $casts =[
        "next_due_date" => "date",
        "credit_card" => "json"
    ];

}
