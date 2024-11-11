<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tour_id' => $this->faker->numberBetween(1, 100), // Assuming there are 100 tours
            'customer_id' => $this->faker->numberBetween(1, 100), // Assuming there are 100 customers
            'booking_date' => $this->faker->date(),
            'number_of_people' => $this->faker->numberBetween(1, 10), // Assuming a booking can have between 1 and 10 people
            'tour_guide_id' => $this->faker->numberBetween(1, 50), // Assuming there are 50 tour guides
        ];
    }
}