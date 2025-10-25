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
        Schema::create('courierriders', function (Blueprint $table) {
            $table->id();
            $table->integer('courieragent_id');
            $table->string('rider_name');
            $table->string('rider_email')->nullable();
            $table->string('rider_phone');
            $table->string('password');
            $table->text('area_address')->nullable();
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
        Schema::dropIfExists('courierriders');
    }
};
