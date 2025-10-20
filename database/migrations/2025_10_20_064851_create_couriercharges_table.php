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
        Schema::create('couriercharges', function (Blueprint $table) {
            $table->id();
            $table->string('inside_delivery_charge')->nullable();
            $table->string('outsider_delivery_charge')->nullable();
            $table->string('per_weight_charge')->nullable();
            $table->string('home_pickup_charge')->nullable();
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
        Schema::dropIfExists('couriercharges');
    }
};
