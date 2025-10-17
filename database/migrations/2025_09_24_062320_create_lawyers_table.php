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
        Schema::create('lawyers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('gender');
            $table->string('dob');
            $table->string('phone')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('license_number')->unique();
            $table->string('total_experience');
            $table->string('practice_area');
            $table->string('current_law_firm')->nullable();
            $table->string('start_time')->nullable();
            $table->string('academic_institute');
            $table->string('lawyerdegrees');
            $table->string('passing_year');
            $table->text('lawyer_bio')->nullable();
            $table->string('refer_code')->nullable();
            $table->string('password');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->string('activation_status')->nullable();
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
        Schema::dropIfExists('lawyers');
    }
};
