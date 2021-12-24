<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PipeDrive extends Model
{
    protected $table = 'pipedrive';

    protected $fillable = [
        'user_id',
        'user_name',
        'organization_id',
        'person_id',
        'deal_id'
    ];

}
