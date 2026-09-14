<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Member;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Chiffres de la maison : produits actifs, commandes à traiter, clients, membres.
 */
class HouseStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -9;

    protected static bool $isDiscovered = false;

    protected static bool $isLazy = false;

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        return [
            Stat::make(__('admin.stats.active_products'), Product::query()->active()->count())
                ->description(__('admin.stats.active_products_hint')),
            Stat::make(__('admin.stats.new_orders'), Order::query()->where('status', OrderStatus::New)->count())
                ->description(__('admin.stats.new_orders_hint')),
            Stat::make(__('admin.stats.customers'), Customer::query()->count())
                ->description(__('admin.stats.customers_hint')),
            Stat::make(__('admin.stats.members'), Member::query()->count())
                ->description(__('admin.stats.members_hint')),
        ];
    }
}
