<?php

namespace Database\Seeders;

use App\Models\ShippingRate;
use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

/**
 * Zones de livraison et tarifs. Prix en centimes. Livraison offerte dès 600 dh.
 * La zone sans villes (« Autres villes ») sert de repli pour toute ville inconnue.
 */
class ShippingZonesSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            ['name' => 'Casablanca', 'cities' => ['Casablanca', 'Mohammedia', 'Bouskoura', 'Dar Bouazza'], 'delay' => '24 à 48 h', 'price' => 3500],
            ['name' => 'Rabat · Salé · Kénitra', 'cities' => ['Rabat', 'Salé', 'Témara', 'Kénitra', 'Skhirat'], 'delay' => '24 à 48 h', 'price' => 3500],
            ['name' => 'Tanger · Tétouan', 'cities' => ['Tanger', 'Tétouan', 'Martil', 'Fnideq', 'M’diq'], 'delay' => '24 à 48 h', 'price' => 3500],
            ['name' => 'Marrakech · Agadir', 'cities' => ['Marrakech', 'Agadir', 'Essaouira', 'Safi', 'El Jadida'], 'delay' => '48 h', 'price' => 3900],
            ['name' => 'Fès · Meknès · Oujda', 'cities' => ['Fès', 'Meknès', 'Oujda', 'Nador', 'Al Hoceïma', 'Ifrane'], 'delay' => '48 h', 'price' => 3900],
            ['name' => 'Autres villes', 'cities' => [], 'delay' => '48 à 72 h', 'price' => 4900],
        ];

        foreach ($zones as $position => $zone) {
            /** @var ShippingZone $model */
            $model = ShippingZone::query()->updateOrCreate(
                ['name' => $zone['name']],
                [
                    'cities' => $zone['cities'],
                    'delay' => $zone['delay'],
                    'position' => $position,
                    'is_active' => true,
                ],
            );

            ShippingRate::query()->updateOrCreate(
                ['shipping_zone_id' => $model->id],
                ['price' => $zone['price'], 'free_threshold' => 60000],
            );
        }
    }
}
