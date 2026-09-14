<?php

namespace App\Filament\Resources\ShippingZones\Pages;

use App\Filament\Resources\ShippingZones\Pages\Concerns\EditsRate;
use App\Filament\Resources\ShippingZones\ShippingZoneResource;
use App\Models\ShippingZone;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShippingZone extends EditRecord
{
    use EditsRate;

    protected static string $resource = ShippingZoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var ShippingZone $zone */
        $zone = $this->getRecord();
        $rate = $zone->rate;

        $data['price'] = $rate?->price;
        $data['free_threshold'] = $rate?->free_threshold;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->extractRate($data);
    }

    protected function afterSave(): void
    {
        $this->saveRate();
    }
}
