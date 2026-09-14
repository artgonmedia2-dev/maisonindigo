<?php

namespace App\Filament\Resources\ShippingZones\Tables;

use App\Models\ShippingZone;
use App\Support\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShippingZonesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->reorderable('position')
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.shipping.fields.name'))
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (ShippingZone $record): string => $record->cities === []
                        ? __('admin.shipping.fields.fallback')
                        : implode(', ', array_slice($record->cities, 0, 4))),

                TextColumn::make('delay')
                    ->label(__('admin.shipping.fields.delay')),

                TextColumn::make('rate.price')
                    ->label(__('admin.shipping.fields.price'))
                    ->formatStateUsing(fn (?int $state): string => $state === null ? '—' : Money::format($state))
                    ->alignEnd(),

                TextColumn::make('rate.free_threshold')
                    ->label(__('admin.shipping.fields.free_threshold'))
                    ->formatStateUsing(fn (?int $state): string => $state === null ? '—' : Money::format($state))
                    ->alignEnd(),

                IconColumn::make('is_active')
                    ->label(__('admin.shipping.fields.is_active'))
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading(__('admin.shipping.empty_heading'))
            ->emptyStateDescription(__('admin.shipping.empty_description'));
    }
}
