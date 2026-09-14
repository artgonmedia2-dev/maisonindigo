<?php

namespace App\Filament\Resources\ShippingZones\Pages;

use App\Filament\Resources\ShippingZones\Pages\Concerns\EditsRate;
use App\Filament\Resources\ShippingZones\ShippingZoneResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShippingZone extends CreateRecord
{
    use EditsRate;

    protected static string $resource = ShippingZoneResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->extractRate($data);
    }

    protected function afterCreate(): void
    {
        $this->saveRate();
    }
}
