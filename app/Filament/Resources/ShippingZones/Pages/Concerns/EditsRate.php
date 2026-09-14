<?php

namespace App\Filament\Resources\ShippingZones\Pages\Concerns;

use App\Models\ShippingZone;

/**
 * Le tarif d'une zone est saisi dans le même formulaire que la zone :
 * une zone, un tarif. Les champs sont extraits avant l'enregistrement,
 * puis écrits sur la relation.
 */
trait EditsRate
{
    /** @var array<string, int|null> */
    protected array $rateData = [];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function extractRate(array $data): array
    {
        $this->rateData = [
            'price' => isset($data['price']) ? (int) $data['price'] : 0,
            'free_threshold' => isset($data['free_threshold']) ? (int) $data['free_threshold'] : null,
        ];

        unset($data['price'], $data['free_threshold']);

        return $data;
    }

    protected function saveRate(): void
    {
        /** @var ShippingZone $zone */
        $zone = $this->getRecord();

        $zone->rates()->updateOrCreate(['shipping_zone_id' => $zone->id], $this->rateData);

        $zone->refresh();
    }
}
