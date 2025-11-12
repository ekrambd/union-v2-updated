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
        Schema::create('riderearnings', function (Blueprint $table) {
            $table->id();
            $table->integer('rider_id');
            $table->integer('order_id')->nullable();
            $table->string('earning_source');
            $table->string('earning_amount');
            $table->string('admin_charge')->nullable();
            $table->string('income_tax')->nullable();
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
        Schema::dropIfExists('riderearnings');
    }
};
