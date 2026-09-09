<?php

namespace Database\Factories;

use App\Models\ShippingZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingZone>
 */
class ShippingZoneFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Casablanca', 'Rabat et Salé', 'Tanger', 'Marrakech', 'Fès', 'Agadir', 'Reste du Maroc']),
            'cities' => [fake()->city()],
            'delay' => '24 à 48 h',
            'position' => 0,
            'is_active' => true,
        ];
    }
}
