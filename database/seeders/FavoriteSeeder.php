<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Favorite;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Favorite::create([
            'user_id' => 4,
            'tour_id' => 1,
        ]);
        // Favorite::create([
        //     'user_id' => 4,
        //     'tour_id' => 2,
        // ]);
        // Favorite::create([
        //     'user_id' => 4,
        //     'tour_id' => 3,
        // ]);
        // Favorite::create([
        //     'user_id' => 4,
        //     'tour_id' => 4,
        // ]);
        // Favorite::create([
        //     'user_id' => 4,
        //     'tour_id' => 5,
        // ]);
        Favorite::create([
            'user_id' => 4,
            'tour_id' => 6,
        ]);

    }
}
