<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankInformations extends Model
{
    use SoftDeletes;

    protected $table = 'bank_informations';

    protected $fillable = [
        'user_id',
        'user_name',
        'for_commissions',
        'account_name',
        'bank_account_type',
        'bank_account_number',
        'owner_name',
        'owner_birth_date',
        'registry_code',
        'bank_code',
        'agency',
        'account',
        'account_digit'
    ];

    protected $casts = [
        'owner_birth_date' => 'date',
    ];
}

