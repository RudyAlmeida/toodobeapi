<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegistrationForms extends Model
{
    use SoftDeletes;

    protected $table = 'registration_forms';

    protected $fillable = [
        'registration_form_type',
        'user_id',
        'name',
        'address_zipcode',
        'address_type',
        'address_type_other_string',
        'address_street',
        'address_number',
        'address_complement',
        'address_neighborhood',
        'address_city',
        'address_state',
        'address_country',
        'address_dwelling_time',
        'phone',
        'marital_status',
        'marital_status_other_string',
        'birthday',
        'citizenship',
        'hometown',
        'mothers_name',
        'fathers_name',
        'professional_category',
        'profession',
        'proven_income',
        'pis',
        'fgts_value',
        'employed',
        'company_name',
        'company_admission_date',
        'declaring_ir',
        'education_level',
        'educational_institution',
        'course',
        'conclusion_year',
        'has_vehicle',
        'vehicle_type',
        'vehicle_type_other_string',
        'vehicle_manufacturer',
        'vehicle_model',
        'vehicle_year',
        'own_property',
        'property_value',
        'businessman',
        'businessman_name',
        'businessman_cnpj',
        'approximate_billing',
        'height',
        'weight',
        'personal_references'
    ];

    protected $casts = [
        'personal_references' => 'json',
        'birthday' => 'date',
        'company_admission_date' => 'date',
    ];

}
