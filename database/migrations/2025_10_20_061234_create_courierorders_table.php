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
        Schema::create('courierorders', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('division_id')->nullable();
            $table->integer('district_id')->nullable();
            $table->integer('upazila_id')->nullable();
            $table->integer('union_id')->nullable();
            $table->enum('area_type', ['inside_city', 'outside_city']);
            $table->string('pickup_location')->nullable();
            $table->text('delivery_full_address')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_phone')->nullable();
            $table->string('weight')->nullable();
            $table->string('coupon')->nullable();
            $table->text('guide_pickup_location')->nullable();
            $table->string('charge_amount')->nullable();
            $table->enum('pay_by', ['sender', 'receiver'])->nullable();
            $table->enum('pickup_type', ['home', 'agent'])->nullable();
            $table->enum('document_type', ['parcel', 'document'])->nullable();
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
        Schema::dropIfExists('courierorders');
    }
};
