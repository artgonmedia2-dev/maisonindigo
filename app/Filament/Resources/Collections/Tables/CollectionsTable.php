<?php

namespace App\Filament\Resources\Collections\Tables;

use App\Enums\CollectionType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CollectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->reorderable('position')
            ->columns([
                TextColumn::make('title')
                    ->label(__('admin.collections.fields.title'))
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('type')
                    ->label(__('admin.collections.fields.type'))
                    ->badge(),

                TextColumn::make('products_count')
                    ->label(__('admin.collections.fields.products_count'))
                    ->counts('products')
                    ->alignCenter(),

                IconColumn::make('is_visible')
                    ->label(__('admin.common.visible'))
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label(__('admin.common.updated_at'))
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('admin.collections.fields.type'))
                    ->options(CollectionType::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading(__('admin.collections.empty_heading'))
            ->emptyStateDescription(__('admin.collections.empty_description'));
    }
}
