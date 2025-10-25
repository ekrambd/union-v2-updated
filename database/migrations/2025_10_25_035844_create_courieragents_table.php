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
        Schema::create('courieragents', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('father_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('nid_passport')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->date('entry_date')->nullable();
            $table->text('education')->nullable();
            $table->text('present_address')->nullable();
            $table->text('shop_address')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_holder_name')->nullable();
            $table->string('account_no')->nullable();
            $table->string('branch_name')->nullable();
            $table->text('branch_location')->nullable();
            $table->string('image')->nullable();
            $table->string('password');
            $table->string('status')->default('Active');
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
        Schema::dropIfExists('courieragents');
    }
};
