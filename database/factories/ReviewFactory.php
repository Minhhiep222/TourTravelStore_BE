<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->numberBetween(1, 10),  // Assuming you have a User factory
            'tour_id' => $this->faker->numberBetween(1, 10),  // Assuming you have a Tour factory
            'rating' => $this->faker->numberBetween(1, 5), // Rating between 1 and 5
            'comment' => $this->faker->sentence(), // Fake sentence as a comment
            'status' => $this->faker->numberBetween(0, 1), // Random status: 0 or 1
            'parent_id' => $this->faker->optional()->randomDigitNotNull(), // Optional, can be null
            'is_approved' => $this->faker->boolean(), // Random boolean for approval
        ];
    }
}