<?php

namespace App\Filament\Widgets;

use App\Enums\ProductStatus;
use App\Filament\Resources\Products\ProductResource;
use App\Models\ProductVariant;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Tailles au seuil d'alerte ou en rupture, sur les produits en ligne.
 */
class LowStockWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected static bool $isDiscovered = false;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('admin.widgets.low_stock'))
            ->query(fn (): Builder => ProductVariant::query()
                ->with('product')
                ->whereColumn('stock', '<=', 'low_stock_threshold')
                ->whereHas('product', fn (Builder $product) => $product->where('status', ProductStatus::Active))
                ->orderBy('stock')
                ->limit(10))
            ->paginated(false)
            ->columns([
                TextColumn::make('product.title')
                    ->label(__('admin.products.singular'))
                    ->weight('bold')
                    ->description(fn (ProductVariant $record): string => $record->product->gender->getLabel()),

                TextColumn::make('label')
                    ->label(__('admin.variants.singular'))
                    ->state(fn (ProductVariant $record): string => $record->label()),

                TextColumn::make('sku')
                    ->label(__('admin.variants.fields.sku'))
                    ->copyable(),

                TextColumn::make('stock')
                    ->label(__('admin.variants.fields.stock'))
                    ->badge()
                    ->alignCenter()
                    ->color(fn (int $state): string => $state === 0 ? 'danger' : 'warning'),
            ])
            ->recordActions([
                Action::make('reassortir')
                    ->label(__('admin.variants.restock'))
                    ->icon('heroicon-o-plus-circle')
                    ->url(fn (ProductVariant $record): string => ProductResource::getUrl('edit', ['record' => $record->product_id])),
            ])
            ->emptyStateHeading(__('admin.widgets.low_stock_empty'));
    }
}
