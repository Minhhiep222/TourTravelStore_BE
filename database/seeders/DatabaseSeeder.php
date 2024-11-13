<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Tour;
use App\Models\Images;
use App\Models\Schedule;
use App\Models\Booking;
use App\Models\Review;
use App\Models\ImageReview;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        // $this->call(TourSeeder::class);
        // $this->call(ImageSeeder::class);
        $this->call(TourGuideSeeder::class,);

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        Payment::factory()->count(50)->create();
        Tour::factory()->count(5)->create(); // Creates 50 payment records
        // Schedule::factory()->count(50)->create(); // Creates 50 payment records
        Images::factory()->count(20)->create();
        Booking::factory()->count(50)->create();
        Review::factory()->count(20)->create();
        ImageReview::factory()->count(20)->create();
    }
}