<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tour>
 */
class TourFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(5), // Generates a random name
            'description' => $this->faker->paragraph(), // Generates a random description
            'duration' => $this->faker->numberBetween(3, 10), // Generates a random duration
            'price' => $this->faker->numberBetween(10000, 700000), // Generates a random price between 100 and 1000
            'start_date' => $this->faker->date(), // Generates a random start date
            'end_date' => $this->faker->date(), // Generates a random end date
            'location' => $this->faker->city() . ', ' . $this->faker->country(), // Generates a random location
            'availability' => $this->faker->boolean(), // Generates a random availability status
        ];
    }
}