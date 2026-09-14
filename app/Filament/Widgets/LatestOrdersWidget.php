<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Les dix dernières commandes, avec accès direct à la fiche.
 */
class LatestOrdersWidget extends TableWidget
{
    protected static ?int $sort = 1;

    protected static bool $isDiscovered = false;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('admin.widgets.recent_orders'))
            ->query(fn (): Builder => Order::query()->with('items')->latest()->limit(10))
            ->paginated(false)
            ->columns([
                TextColumn::make('number')
                    ->label(__('admin.orders.fields.number'))
                    ->weight('bold'),

                TextColumn::make('customer_name')
                    ->label(__('admin.orders.fields.name'))
                    ->state(fn (Order $record): string => (string) data_get($record->shipping_address, 'name'))
                    ->description(fn (Order $record): string => (string) data_get($record->shipping_address, 'city')),

                TextColumn::make('total')
                    ->label(__('admin.orders.fields.total'))
                    ->formatStateUsing(fn (int $state): string => Money::format($state))
                    ->alignEnd(),

                TextColumn::make('payment_method')
                    ->label(__('admin.orders.fields.payment_method'))
                    ->badge(),

                TextColumn::make('status')
                    ->label(__('admin.orders.fields.status'))
                    ->badge(),

                TextColumn::make('created_at')
                    ->label(__('admin.orders.fields.created_at'))
                    ->since(),
            ])
            ->recordActions([
                Action::make('voir')
                    ->label(__('admin.orders.singular'))
                    ->icon('heroicon-o-eye')
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateHeading(__('admin.orders.empty_heading'))
            ->emptyStateDescription(__('admin.orders.empty_description'));
    }
}
