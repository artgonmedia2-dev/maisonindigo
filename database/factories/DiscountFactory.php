<?php

namespace Database\Factories;

use App\Enums\DiscountType;
use App\Models\Discount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Discount>
 */
class DiscountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('????????')),
            'name' => 'Code de bienvenue',
            'type' => DiscountType::Percent,
            'value' => 10,
            'rules' => null,
            'starts_at' => null,
            'ends_at' => null,
            'usage_limit' => null,
            'usage_count' => 0,
            'active' => true,
        ];
    }

    /**
     * Remise automatique 2 jeans -10 %.
     */
    public function bundle(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => null,
            'name' => 'Deux jeans, dix pour cent',
            'type' => DiscountType::Bundle,
            'value' => 10,
            'rules' => ['min_qty' => 2],
        ]);
    }

    public function fixed(int $cents): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => DiscountType::Fixed,
            'value' => $cents,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['active' => false]);
    }
}
