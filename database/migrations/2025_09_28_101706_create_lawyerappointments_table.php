<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lawyerappointments', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('lawyer_id');
            $table->date('booking_date');
            $table->date('booking_time');
            $table->string('shift')->nullable();
            $table->string('appointment_day')->nullable();
            $table->string('possiblity_time')->nullable();
            $table->string('appointment_date')->nullable();
            $table->string('call_time')->nullable();
            $table->string('call_duration')->nullable();
            $table->string('serial')->nullable();
            $table->string('timestamp');
            $table->string('status')->default('Pending');
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
        Schema::dropIfExists('lawyerappointments');
    }
};
