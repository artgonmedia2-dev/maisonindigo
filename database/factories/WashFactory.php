<?php

namespace Database\Factories;

use App\Models\Wash;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Wash>
 */
class WashFactory extends Factory
{
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->word());

        return [
            'slug' => Str::slug($name, '_'),
            'name' => $name,
            'sku_code' => Str::upper(Str::substr($name, 0, 3)),
            'position' => fake()->numberBetween(1, 200),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
