<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Models\Customer;
use App\Models\Member;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

/**
 * Chiffres de la maison : catalogue, commandes à traiter, chiffre du mois, réassort.
 */
class HouseStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -9;

    protected static bool $isDiscovered = false;

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = null;

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $newOrders = Order::query()->where('status', OrderStatus::New)->count();

        $revenue = (int) Order::query()
            ->whereIn('status', [OrderStatus::Confirmed, OrderStatus::Prepared, OrderStatus::Shipped, OrderStatus::Delivered])
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total');

        $lowStock = ProductVariant::query()
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->whereHas('product', fn (Builder $product) => $product->where('status', ProductStatus::Active))
            ->count();

        return [
            Stat::make(__('admin.stats.active_products'), Product::query()->active()->count())
                ->description(__('admin.stats.active_products_hint')),

            Stat::make(__('admin.stats.new_orders'), $newOrders)
                ->description(__('admin.stats.new_orders_hint'))
                ->color($newOrders > 0 ? 'warning' : 'gray'),

            Stat::make(__('admin.stats.revenue'), Money::format($revenue))
                ->description(__('admin.stats.revenue_hint')),

            Stat::make(__('admin.stats.low_stock'), $lowStock)
                ->description(__('admin.stats.low_stock_hint'))
                ->color($lowStock > 0 ? 'danger' : 'success'),

            Stat::make(__('admin.stats.customers'), Customer::query()->count())
                ->description(__('admin.stats.customers_hint')),

            Stat::make(__('admin.stats.members'), Member::query()->count())
                ->description(__('admin.stats.members_hint')),
        ];
    }

    /**
     * @return int|array<string, int|null>|null
     */
    protected function getColumns(): int|array|null
    {
        return 3;
    }
}
