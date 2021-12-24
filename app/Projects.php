<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Projects extends Model
{
    use SoftDeletes;

    protected $table = 'projects';

    protected $fillable = [
        'user_id',
        'user_name',
        'project_desciption',
        'project_value',
        'project_status',
        'property_type',
        'bedrooms',
        'parking_spaces',
        'leisure_sport',
        'amenities_services',
        'safety',
        'rooms'
    ];

    protected $casts = [
        'property_type' => 'json',
        'leisure_sport' => 'json',
        'amenities_services' => 'json',
        'safety' => 'json',
        'rooms' => 'json'
    ];

}
