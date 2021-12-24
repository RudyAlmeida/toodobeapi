<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role')->default('customer');
            $table->string('affiliate_code');
            $table->string('referred_code')->nullable();
            $table->string('mobile');
            $table->string('affiliate_type');
            $table->decimal('property_value', 10,2)->nullable();
            $table->decimal('first_installment_of_property', 10,2)->nullable();
            $table->decimal('last_installment_of_property', 10,2)->nullable();
            $table->decimal('expected_income', 10,2)->nullable();
            $table->date('birthday');
            $table->string('address_city');
            $table->string('address_state');
            $table->string('address_country');
            $table->string('payment_gateway_id')->nullable();
            $table->string('registry_code')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
