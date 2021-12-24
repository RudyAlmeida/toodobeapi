<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('user_name');
            $table->string('payment_gateway_subscription_id')->nullable();
            $table->string('payment_gateway_customer_id');
            $table->string('payment_gateway_billing_id')->nullable();
            $table->date('due_date')->nullable();
            $table->date('original_due_date')->nullable();
            $table->date('client_payment_date')->nullable();
            $table->decimal('value', 10,2);
            $table->string('billing_type');
            $table->string('status')->default('PENDING');
            $table->string('description')->nullable();
            $table->string('invoice_url')->nullable();
            $table->string('bankslip_url')->nullable();
            $table->json('credit_card')->nullable();
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
        Schema::dropIfExists('billing');
    }
}
