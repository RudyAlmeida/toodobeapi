<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegistrationFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('registration_forms', function (Blueprint $table) {
            $table->id();
            $table->string('registration_form_type');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('name')->nullable();
            $table->string('address_zipcode')->nullable();
            $table->string('address_type')->nullable();
            $table->string('address_type_other_string')->nullable();
            $table->string('address_street')->nullable();
            $table->string('address_number')->nullable();
            $table->string('address_complement')->nullable();
            $table->string('address_neighborhood')->nullable();
            $table->string('address_city')->nullable();
            $table->string('address_state')->nullable();
            $table->string('address_country')->nullable();
            $table->string('address_dwelling_time')->nullable();

            $table->string('phone')->nullable();

            $table->string('marital_status')->nullable();
            $table->string('marital_status_other_string')->nullable();
            $table->date('birthday')->nullable();
            $table->string('citizenship')->nullable();
            $table->string('hometown')->nullable();
            $table->string('mothers_name')->nullable();
            $table->string('fathers_name')->nullable();

            $table->string('professional_category')->nullable();
            $table->string('profession')->nullable();
            $table->decimal('proven_income', 10,2)->nullable();
            $table->string('pis')->nullable();
            $table->decimal('fgts_value', 10, 2)->nullable();
            $table->boolean('employed')->default(false);
            $table->string('company_name')->nullable();
            $table->date('company_admission_date')->nullable();
            $table->boolean('declaring_ir')->default(false);

            $table->string('education_level')->nullable();
            $table->string('educational_institution')->nullable();
            $table->string('course')->nullable();
            $table->year('conclusion_year')->nullable();

            $table->boolean('has_vehicle')->default(false);
            $table->string('vehicle_type')->nullable();
            $table->string('vehicle_type_other_string')->nullable();
            $table->string('vehicle_manufacturer')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->year('vehicle_year')->nullable();

            $table->boolean('own_property')->default(false);
            $table->decimal('property_value', 10, 2)->nullable();

            $table->boolean('businessman')->default(false);
            $table->string('businessman_name')->nullable();
            $table->string('businessman_cnpj')->nullable();
            $table->decimal('approximate_billing', 10,2)->nullable();

            $table->string('height')->nullable();
            $table->string('weight')->nullable();

            $table->json('personal_references')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('registration_forms');
    }
}
