<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tour_id' => $this->faker->numberBetween(1, 100), // Giả sử có 100 tour
            'number_of_tickers' => $this->faker->numberBetween(1, 10),
            'total_price' => $this->faker->randomFloat(2, 100, 1000), // Giá từ 100 đến 1000
            'user_id' => $this->faker->numberBetween(1, 50), // Giả sử có 50 người dùng
            'payment_method' => $this->faker->randomElement(['transfer', 'cash']),
            'status' => $this->faker->randomElement(['pending', 'completed', 'failed', 'refunded']),
            'notes' => $this->faker->sentence(),
            'transaction_id' => $this->faker->uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}