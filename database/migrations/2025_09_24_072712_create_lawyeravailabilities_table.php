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
        Schema::create('lawyeravailabilities', function (Blueprint $table) {
            $table->id();
            $table->integer('lawyer_id');
            $table->string('morning_start_time')->nullable();
            $table->string('morning_end_time')->nullable();
            $table->string('morning_shift_days')->nullable();
            $table->string('afternoon_start_time')->nullable();
            $table->string('afternoon_end_time')->nullable();
            $table->string('afternoon_shift_days')->nullable();
            $table->string('evening_start_time')->nullable();
            $table->string('evening_end_time')->nullable();
            $table->string('evening_shift_days')->nullable();
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
        Schema::dropIfExists('lawyeravailabilities');
    }
};
