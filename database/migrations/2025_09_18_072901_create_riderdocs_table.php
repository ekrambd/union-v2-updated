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
        Schema::create('riderdocs', function (Blueprint $table) {
            $table->id();
            $table->integer('rider_id');
            $table->string('nid_front_photo')->nullable();
            $table->string('nid_back_photo')->nullable();
            $table->string('driving_license_one')->nullable();
            $table->string('driving_license_two')->nullable();
            $table->string('vehicle_license_one')->nullable();
            $table->string('vehicle_license_two')->nullable();
            $table->string('profile_image')->nullable();
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
        Schema::dropIfExists('riderdocs');
    }
};
