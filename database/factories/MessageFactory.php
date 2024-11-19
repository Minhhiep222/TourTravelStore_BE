<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sender_id' =>$this->faker->numberBetween(1, 5) ,  // Tạo user tự động với factory (nếu chưa có user)
            'conversation_id' =>$this->faker->numberBetween(1, 5) ,  // Tạo user tự động với factory (nếu chưa có user)
            'message' => $this->faker->text(200),
        ];
    }
}