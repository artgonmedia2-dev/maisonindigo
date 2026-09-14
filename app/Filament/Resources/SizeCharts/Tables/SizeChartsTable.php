<?php

namespace App\Filament\Resources\SizeCharts\Tables;

use App\Enums\Gender;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SizeChartsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('title')
            ->columns([
                TextColumn::make('title')
                    ->label(__('admin.size_charts.fields.title'))
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('gender')
                    ->label(__('admin.size_charts.fields.gender'))
                    ->badge(),

                TextColumn::make('cut')
                    ->label(__('admin.size_charts.fields.cut'))
                    ->badge(),

                TextColumn::make('rows_count')
                    ->label(__('admin.size_charts.fields.rows_count'))
                    ->counts('rows')
                    ->alignCenter(),

                TextColumn::make('products_count')
                    ->label(__('admin.products.plural'))
                    ->counts('products')
                    ->alignCenter()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('gender')
                    ->label(__('admin.size_charts.fields.gender'))
                    ->options(Gender::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
