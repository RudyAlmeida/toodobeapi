<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('user_name');
            $table->string('project_desciption');
            $table->decimal('project_value',10,2);
            $table->string('project_status')->default('iniciado');
            $table->json('property_type')->nullable();
            $table->integer('bedrooms')->nullable();
            $table->integer('parking_spaces')->nullable();
            $table->json('leisure_sport')->nullable();
            $table->json('amenities_services')->nullable();
            $table->json('safety')->nullable();
            $table->json('rooms')->nullable();
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
        Schema::dropIfExists('projects');
    }
}