<?php

namespace App\Filament\Resources\Cuts\Tables;

use App\Enums\Gender;
use App\Models\Cut;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.cuts.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('slug')
                    ->label(__('admin.cuts.fields.slug'))
                    ->searchable()
                    ->color('gray'),

                TextColumn::make('sku_code')
                    ->label(__('admin.cuts.fields.sku_code'))
                    ->badge(),

                TextColumn::make('genders')
                    ->label(__('admin.cuts.fields.genders'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Gender::tryFrom($state)?->getLabel() ?? $state),

                TextColumn::make('products_count')
                    ->label(__('admin.cuts.fields.products_count'))
                    ->counts('products')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label(__('admin.cuts.fields.is_active'))
                    ->boolean(),

                TextColumn::make('position')
                    ->label(__('admin.cuts.fields.position'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label(__('admin.cuts.fields.is_active')),
            ])
            ->recordActions([
                EditAction::make(),
                // Supprimer une coupe employée orphelinerait les produits :
                // on propose plutôt de la retirer de la vente.
                DeleteAction::make()
                    ->before(function (Cut $record, DeleteAction $action): void {
                        if (! $record->isUsed()) {
                            return;
                        }

                        Notification::make()
                            ->danger()
                            ->title(__('admin.cuts.in_use', ['count' => $record->products()->count()]))
                            ->send();

                        $action->cancel();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading(__('admin.cuts.empty_heading'))
            ->emptyStateDescription(__('admin.cuts.empty_description'));
    }
}
