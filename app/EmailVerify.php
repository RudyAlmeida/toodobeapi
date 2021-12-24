<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailVerify extends Model
{
    protected $fillable = [
        'email', 'token'
    ];
}
