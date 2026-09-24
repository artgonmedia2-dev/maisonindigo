<?php

namespace Database\Factories;

use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'author_name' => fake()->firstName().' '.mb_substr(fake()->lastName(), 0, 1).'.',
            'city' => fake()->randomElement(['Casablanca', 'Rabat', 'Tanger', 'Marrakech', 'Agadir', 'Oujda', 'Nador']),
            'rating' => fake()->numberBetween(4, 5),
            'body' => 'La toile est dense, la coupe tombe comme sur les photos. Reçu en deux jours.',
            'size_bought' => '32/32',
            'product_id' => null,
            'order_id' => null,
            'published_at' => now()->subDays(fake()->numberBetween(1, 60)),
            'position' => fake()->numberBetween(1, 100),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['published_at' => null]);
    }
}
