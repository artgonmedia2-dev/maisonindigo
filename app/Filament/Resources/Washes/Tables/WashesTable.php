<?php

namespace App\Filament\Resources\Washes\Tables;

use App\Models\Wash;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class WashesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.washes.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('slug')
                    ->label(__('admin.washes.fields.slug'))
                    ->searchable()
                    ->color('gray'),

                TextColumn::make('sku_code')
                    ->label(__('admin.washes.fields.sku_code'))
                    ->badge(),

                TextColumn::make('products_count')
                    ->label(__('admin.washes.fields.products_count'))
                    ->counts('products')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label(__('admin.washes.fields.is_active'))
                    ->boolean(),

                TextColumn::make('position')
                    ->label(__('admin.washes.fields.position'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label(__('admin.washes.fields.is_active')),
            ])
            ->recordActions([
                EditAction::make(),
                // Supprimer un lavage employé orphelinerait les produits :
                // on propose plutôt de le retirer de la vente.
                DeleteAction::make()
                    ->before(function (Wash $record, DeleteAction $action): void {
                        if (! $record->isUsed()) {
                            return;
                        }

                        Notification::make()
                            ->danger()
                            ->title(__('admin.washes.in_use', ['count' => $record->products()->count()]))
                            ->send();

                        $action->cancel();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading(__('admin.washes.empty_heading'))
            ->emptyStateDescription(__('admin.washes.empty_description'));
    }
}
