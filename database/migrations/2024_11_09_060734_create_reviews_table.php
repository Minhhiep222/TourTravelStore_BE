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
        Schema::create('reviews', function (Blueprint $table): void {
            $table->id();
            $table->integer('user_id');
            $table->integer('tour_id');
            $table->tinyInteger('rating')->nullable();
            $table->text('comment');
            $table->timestamps();
            $table->tinyInteger('status')->default(1)->nullable();
            $table->foreignId('parent_id')->nullable();
            $table->boolean('is_approved')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};