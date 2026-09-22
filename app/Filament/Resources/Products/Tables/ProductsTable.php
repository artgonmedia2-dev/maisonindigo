<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\Gender;
use App\Enums\ProductStatus;
use App\Models\Cut;
use App\Models\Product;
use App\Models\Wash;
use App\Support\CatalogTerms;
use App\Support\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                SpatieMediaLibraryImageColumn::make('gallery')
                    ->label('')
                    ->collection(Product::MEDIA_GALLERY)
                    ->limit(1)
                    ->imageWidth(44)
                    ->imageHeight(55),

                TextColumn::make('title')
                    ->label(__('admin.products.fields.title'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Product $record): string => $record->gender->getLabel()),

                TextColumn::make('wash')
                    ->label(__('admin.products.fields.wash'))
                    ->badge()
                    ->toggleable(),

                TextColumn::make('price')
                    ->label(__('admin.products.fields.price'))
                    ->formatStateUsing(fn (int $state): string => Money::format($state))
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('variants_count')
                    ->label(__('admin.products.fields.variants'))
                    ->counts('variants')
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('stock')
                    ->label(__('admin.products.fields.stock'))
                    ->state(fn (Product $record): int => (int) $record->variants()->sum('stock'))
                    ->alignCenter()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'danger',
                        $state < 20 => 'warning',
                        default => 'success',
                    }),

                IconColumn::make('is_new')
                    ->label(__('admin.products.fields.is_new'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_atelier')
                    ->label(__('admin.products.fields.is_atelier'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label(__('admin.products.fields.status'))
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('gender')
                    ->label(__('admin.products.fields.gender'))
                    ->options(Gender::class),

                SelectFilter::make('cut')
                    ->label(__('admin.products.fields.cut'))
                    ->options(fn (): array => app(CatalogTerms::class)->cuts()
                        ->mapWithKeys(fn (Cut $cut): array => [$cut->slug => $cut->name])
                        ->all())
                    ->multiple(),

                SelectFilter::make('wash')
                    ->label(__('admin.products.fields.wash'))
                    ->options(fn (): array => app(CatalogTerms::class)->washes()
                        ->mapWithKeys(fn (Wash $wash): array => [$wash->slug => $wash->name])
                        ->all())
                    ->multiple(),

                SelectFilter::make('status')
                    ->label(__('admin.products.fields.status'))
                    ->options(ProductStatus::class),

                Filter::make('low_stock')
                    ->label(__('admin.products.filters.low_stock'))
                    ->query(fn (Builder $query): Builder => $query->whereHas(
                        'variants',
                        fn (Builder $variants) => $variants->whereColumn('stock', '<=', 'low_stock_threshold'),
                    )),

                Filter::make('out_of_stock')
                    ->label(__('admin.products.filters.out_of_stock'))
                    ->query(fn (Builder $query): Builder => $query->whereDoesntHave(
                        'variants',
                        fn (Builder $variants) => $variants->where('stock', '>', 0),
                    )),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading(__('admin.products.empty_heading'))
            ->emptyStateDescription(__('admin.products.empty_description'));
    }
}
