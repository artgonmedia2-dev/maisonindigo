<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use App\Models\StockAlert;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockAlert>
 */
class StockAlertFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_variant_id' => ProductVariant::factory(),
            'email' => fake()->safeEmail(),
            'phone' => null,
            'notified_at' => null,
        ];
    }

    public function notified(): static
    {
        return $this->state(fn (array $attributes) => ['notified_at' => now()]);
    }
}
