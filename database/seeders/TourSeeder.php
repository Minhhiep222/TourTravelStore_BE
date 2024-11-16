<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tours = [
            [
                'name' => 'Beach Paradise',
                'description' => 'Relax at the beautiful beach with sunbathing, swimming, and water sports. Enjoy beach volleyball, paddleboarding, and a refreshing drink at a beach bar while watching the sunset.',
                'duration' => '3',
                'price' => 780000,
                'price_children' => 250000,
                'start_date' => '2024-06-01',
                'end_date' => '2024-06-01',
                'location' => 'Bahamas',
                'availability' => true,
                'status'=>1,
            ],
            [
                'name' => 'Mountain Adventure',
                'description' => 'A thrilling hike through majestic mountains with breathtaking views, lush forests, and expert guides. Enjoy campfire evenings and learn about local ecology and history.',
                'duration' => '5',
                'price' => 880000,
                'price_children' => 440000,
                'start_date' => '2024-07-10',
                'end_date' => '2024-07-13',
                'location' => 'Rocky Mountains',
                'availability' => true,
                'status'=>1,
            ],
            [
                'name' => 'City Tour',
                'description' => 'Explore New York’s famous landmarks like Central Park and the Statue of Liberty. Enjoy guided tours, art, food, and lively street performances.',
                'duration' => '4',
                'price' => 600000,
                'price_children' => 300000,
                'start_date' => '2024-08-15',
                'end_date' => '2024-08-16',
                'location' => 'New York',
                'availability' => false,
                'status'=>2,
            ],
            [
                'name' => 'Desert Safari',
                'description' => 'Experience a thrilling desert safari with dune bashing, camel rides, and cultural insights. Enjoy a BBQ dinner and activities like sandboarding and henna painting.',
                'duration' => '5',
                'price' => 70000,
                'price_children' => 300000,
                'start_date' => '2024-09-01',
                'end_date' => '2024-09-01',
                'location' => 'Dubai',
                'availability' => true,
                'status'=>1,
            ],
            [
                'name' => 'Historical Tour',
                'description' => 'Visit UNESCO World Heritage Sites and learn about ancient civilizations. Explore archaeological sites and hear captivating stories from local historians.',
                'duration' => '5',
                'price' => 950000,
                'price_children' => 450000,
                'start_date' => '2024-10-01',
                'end_date' => '2024-10-05',
                'location' => 'Greece',
                'availability' => true,
                'status'=>1,
            ],
            [
                'name' => 'Historical Tour 2',
                'description' => 'Visit UNESCO World Heritage Sites and learn about ancient civilizations. Explore archaeological sites and hear captivating stories from local historians.',
                'duration' => '5',
                'price' => 950000,
                'price_children' => 450000,
                'start_date' => '2024-10-01',
                'end_date' => '2024-10-05',
                'location' => 'Greece',
                'availability' => false,
                'status'=>2,
            ],
            [
                'name' => 'Historical Tour 3',
                'description' => 'Visit UNESCO World Heritage Sites and learn about ancient civilizations. Explore archaeological sites and hear captivating stories from local historians.',
                'duration' => '5',
                'price' => 9000,
                'price_children' => 45000,
                'start_date' => '2024-10-01',
                'end_date' => '2024-10-05',
                'location' => 'Greece',
                'availability' => false,
                'status'=>2,
            ],
            [
                'name' => 'City Tour 2',
                'description' => 'Explore New York’s famous landmarks like Central Park and the Statue of Liberty. Enjoy guided tours, art, food, and lively street performances.',
                'duration' => '4',
                'price' => 60000,
                'price_children' => 30000,
                'start_date' => '2024-08-15',
                'end_date' => '2024-08-16',
                'location' => 'New York',
                'availability' => false,
                'status'=>2,
            ],
            // [
            //     'name' => 'Wildlife Safari',
            //     'description' => 'Get close to majestic wildlife in Kenya on thrilling game drives. Spot the Big Five, learn about conservation, and enjoy evenings around a campfire.',
            //     'duration' => '4 Days',
            //     'price' => 6000000,
            //     'start_date' => '2024-11-01',
            //     'end_date' => '2024-11-04',
            //     'location' => 'Kenya',
            //     'availability' => true,
            // ],
            // [
            //     'name' => 'Culinary Tour',
            //     'description' => 'Explore Italy’s best traditional dishes and local markets. Participate in hands-on cooking classes and savor authentic meals paired with exquisite wines.',
            //     'duration' => '3 Days',
            //     'price' => 3300000,
            //     'start_date' => '2024-12-01',
            //     'end_date' => '2024-12-03',
            //     'location' => 'Italy',
            //     'availability' => true,
            // ],
            // [
            //     'name' => 'Ski Trip',
            //     'description' => 'Enjoy skiing and snowboarding at top resorts in Switzerland. Explore mountains at your own pace, with group lessons, après-ski fun, and cozy lodge evenings.',
            //     'duration' => '1 Week',
            //     'price' => 1100000,
            //     'start_date' => '2024-12-15',
            //     'end_date' => '2024-12-22',
            //     'location' => 'Switzerland',
            //     'availability' => false,
            // ],
            // [
            //     'name' => 'Island Hopping',
            //     'description' => 'Visit stunning islands in Thailand for snorkeling, swimming, and exploring vibrant local cultures. Enjoy Thai hospitality, street food, and beautiful beaches.',
            //     'duration' => '4 Days',
            //     'price' => 3300000,
            //     'start_date' => '2025-01-05',
            //     'end_date' => '2025-01-08',
            //     'location' => 'Thailand',
            //     'availability' => true,
            // ],
            // [
            //     'name' => 'Cruise Experience',
            //     'description' => 'Relax on a luxurious Caribbean cruise with gourmet dining, entertainment, and exciting onboard activities. Explore beautiful destinations with unique excursions.',
            //     'duration' => '1 Week',
            //     'price' => 4400000,
            //     'start_date' => '2025-02-01',
            //     'end_date' => '2025-02-08',
            //     'location' => 'Caribbean',
            //     'availability' => true,
            // ],
        ];

        DB::table('tours')->insert($tours);
    }
}
