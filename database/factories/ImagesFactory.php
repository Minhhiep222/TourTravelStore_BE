<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ImagesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tour_id' => $this->faker->numberBetween(1, 5), // Assuming there are 100 tours
            'image_url' => $this->faker->randomElement([
        "1730963798_8fsg2NVXzSV4JKqdJZqKQukNUfHWETJcJha6ZVHtfKIdcXbnA.jpg",
        "1730963285_3JtKzhCAUUqjE5bIG9FCrvkGfycRFSu5L01RVPi8LIuI312JA.jpg",
        "1730963302_cMfPdFNZGOWdCKz9dYsuhvrvDwKXEXH5m8bMZj5MPWw1a22JA.jpg",
    ]), // Generates a random image URL
            'alt_text' => $this->faker->sentence(), // Generates a random sentence for alt text
        ];
    }
}