<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\Pages\Concerns\ComposesProduct;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    use ComposesProduct;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('voir')
                ->label(__('admin.common.view_on_shop'))
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn (Product $record): string => url($record->path()))
                ->openUrlInNewTab()
                ->visible(fn (Product $record): bool => $record->isActive()),
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->composeTitleAndSlug($data);
    }

    protected function afterSave(): void
    {
        $this->nameMediaViews();
    }
}
