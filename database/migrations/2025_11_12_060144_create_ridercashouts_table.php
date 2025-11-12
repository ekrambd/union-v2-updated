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
        Schema::create('ridercashouts', function (Blueprint $table) {
            $table->id();
            $table->integer('rider_id');
            $table->string('amount');
            $table->string('status')->default('pending');
            $table->string('transaction_id')->unique()->nullable();
            $table->string('payment_method');
            $table->string('account_number');
            $table->date('request_date');
            $table->string('request_time');
            $table->date('confirm_date')->nullable();
            $table->string('confirm_time')->nullable();
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
        Schema::dropIfExists('ridercashouts');
    }
};
