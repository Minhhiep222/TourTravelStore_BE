<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TourGuide;

class TourGuideSeeder extends Seeder
{
    public function run()
    {
        // Create five tour guides
        TourGuide::create([
            'name' => 'Alice Smith',
            'email' => 'alice@example.com',
            'phone' => '0329169799',
            'experience' => 3.5,
        ]);

        TourGuide::create([
            'name' => 'Bob Johnson',
            'email' => 'bob@example.com',
            'phone' => '2223334444',
            'experience' => 5.0,
        ]);

        TourGuide::create([
            'name' => 'Carol Williams',
            'email' => 'carol@example.com',
            'phone' => '3334445555',
            'experience' => 2.0,
        ]);

        TourGuide::create([
            'name' => 'David Brown',
            'email' => 'david@example.com',
            'phone' => '4445556666',
            'experience' => 4.5,
        ]);

        TourGuide::create([
            'name' => 'Eve Davis',
            'email' => 'eve@example.com',
            'phone' => '5556667777',
            'experience' => 1.5,
        ]);
    }
}
