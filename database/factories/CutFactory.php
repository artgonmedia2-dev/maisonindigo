<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Models\Cut;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Cut>
 */
class CutFactory extends Factory
{
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->word());

        return [
            'slug' => Str::slug($name, '_'),
            'name' => $name,
            'sku_code' => Str::upper(Str::substr($name, 0, 3)),
            'genders' => array_map(fn (Gender $gender): string => $gender->value, Gender::cases()),
            'position' => fake()->numberBetween(1, 200),
            'is_active' => true,
        ];
    }

    public function forGender(Gender $gender): static
    {
        return $this->state(fn (array $attributes) => ['genders' => [$gender->value]]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
