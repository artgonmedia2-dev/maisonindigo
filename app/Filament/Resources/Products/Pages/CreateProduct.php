<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\Pages\Concerns\ComposesProduct;
use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    use ComposesProduct;

    protected static string $resource = ProductResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->composeTitleAndSlug($data);
    }

    protected function afterCreate(): void
    {
        $this->nameMediaViews();
    }

    protected function getRedirectUrl(): string
    {
        // On enchaîne sur les tailles : un produit sans variante ne se vend pas.
        return static::getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
