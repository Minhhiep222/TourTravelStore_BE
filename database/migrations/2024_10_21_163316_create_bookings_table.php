<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->Integer('tour_id');
            $table->Integer('customer_id');
            $table->date('booking_date')->nullable();
            $table->integer('number_of_people');
            $table->integer('number_of_adult')->nullable();
            $table->integer('number_of_childrent')->nullable();
            $table->integer('total_price')->nullable();
            $table->Integer('tour_guide_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};