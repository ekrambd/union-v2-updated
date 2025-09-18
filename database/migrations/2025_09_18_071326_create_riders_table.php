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
        Schema::create('riders', function (Blueprint $table) {
            $table->id();
            $table->string("full_name");
            $table->string("nid_passport")->unique();
            $table->string("dob");
            $table->string("email")->unique()->nullable();
            $table->string("gender");
            $table->integer("riderarea_id")->nullable();
            $table->string("vehicle");
            $table->string("phone")->unique()->nullable();
            $table->string("license_number")->unique()->nullable();
            $table->string("reg_series");
            $table->string("reg_no");
            $table->string("refer_code")->nullable();
            $table->string("reffaral_code");
            $table->string('password');
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
        Schema::dropIfExists('riders');
    }
};
