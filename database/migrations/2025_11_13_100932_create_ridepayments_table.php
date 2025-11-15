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
        Schema::create('ridepayments', function (Blueprint $table) {
            $table->id();
            $table->integer('rider_id');
            $table->string('amount');
            $table->string('payment_method');
            $table->string('account_number')->nullable();
            $table->string('transaction_id');
            $table->date('date');
            $table->string('time');
            $table->string('status')->default('pending');
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
        Schema::dropIfExists('ridepayments');
    }
};
