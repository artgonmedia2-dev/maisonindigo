<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'name' => fake()->name(),
            'phone' => '+2126'.fake()->numerify('########'),
            'line1' => fake()->streetAddress(),
            'line2' => null,
            'city' => fake()->randomElement(['Casablanca', 'Rabat', 'Tanger', 'Marrakech', 'Fès']),
            'region' => null,
            'shipping_zone_id' => null,
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => ['is_default' => true]);
    }
}
