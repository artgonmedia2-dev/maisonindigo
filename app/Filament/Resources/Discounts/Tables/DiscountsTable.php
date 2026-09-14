<?php

namespace App\Filament\Resources\Discounts\Tables;

use App\Enums\DiscountType;
use App\Models\Discount;
use App\Support\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DiscountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.discounts.fields.name'))
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('code')
                    ->label(__('admin.discounts.fields.code'))
                    ->badge()
                    ->copyable()
                    ->placeholder(__('admin.discounts.fields.automatic')),

                TextColumn::make('type')
                    ->label(__('admin.discounts.fields.type'))
                    ->badge(),

                TextColumn::make('value')
                    ->label(__('admin.discounts.fields.value'))
                    ->formatStateUsing(fn (int $state, Discount $record): string => $record->type === DiscountType::Fixed
                        ? Money::format($state)
                        : "{$state} %")
                    ->alignEnd(),

                TextColumn::make('usage_count')
                    ->label(__('admin.discounts.fields.usage_count'))
                    ->formatStateUsing(fn (int $state, Discount $record): string => $record->usage_limit === null
                        ? (string) $state
                        : "{$state} / {$record->usage_limit}")
                    ->alignCenter(),

                TextColumn::make('ends_at')
                    ->label(__('admin.discounts.fields.ends_at'))
                    ->dateTime('d/m/Y')
                    ->placeholder('—')
                    ->toggleable(),

                IconColumn::make('active')
                    ->label(__('admin.discounts.fields.active'))
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('admin.discounts.fields.type'))
                    ->options(DiscountType::class),

                TernaryFilter::make('active')
                    ->label(__('admin.discounts.fields.active')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading(__('admin.discounts.empty_heading'))
            ->emptyStateDescription(__('admin.discounts.empty_description'));
    }
}
