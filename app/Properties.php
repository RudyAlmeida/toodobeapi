<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Properties extends Model
{
    protected $table = 'properties';

    protected $fillable = [
        'property_value',
        'first_installment',
        'last_installment',
        'income_value'
    ];
}

