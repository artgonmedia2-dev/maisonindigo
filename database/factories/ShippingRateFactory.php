<?php

namespace Database\Factories;

use App\Models\ShippingRate;
use App\Models\ShippingZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingRate>
 */
class ShippingRateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shipping_zone_id' => ShippingZone::factory(),
            'price' => 3500,
            'free_threshold' => 60000,
        ];
    }
}
