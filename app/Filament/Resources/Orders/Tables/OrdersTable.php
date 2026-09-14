<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Support\Money;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('number')
                    ->label(__('admin.orders.fields.number'))
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('customer_name')
                    ->label(__('admin.orders.fields.name'))
                    ->state(fn (Order $record): string => (string) data_get($record->shipping_address, 'name'))
                    ->description(fn (Order $record): string => (string) data_get($record->shipping_address, 'city')),
                TextColumn::make('items_count')
                    ->label(__('admin.orders.fields.items'))
                    ->state(fn (Order $record): int => (int) $record->items->sum('qty'))
                    ->alignCenter(),
                TextColumn::make('total')
                    ->label(__('admin.orders.fields.total'))
                    ->formatStateUsing(fn (int $state): string => Money::format($state))
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label(__('admin.orders.fields.payment_method'))
                    ->badge(),
                TextColumn::make('status')
                    ->label(__('admin.orders.fields.status'))
                    ->badge(),
                TextColumn::make('created_at')
                    ->label(__('admin.orders.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('admin.orders.fields.status'))
                    ->options(OrderStatus::class),
                SelectFilter::make('payment_method')
                    ->label(__('admin.orders.fields.payment_method'))
                    ->options(PaymentMethod::class),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->emptyStateHeading(__('admin.orders.empty_heading'))
            ->emptyStateDescription(__('admin.orders.empty_description'));
    }
}
