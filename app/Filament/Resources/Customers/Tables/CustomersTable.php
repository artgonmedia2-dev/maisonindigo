<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Support\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.customers.fields.name'))
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (Customer $record): string => (string) $record->email),

                TextColumn::make('phone')
                    ->label(__('admin.customers.fields.phone'))
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('orders_count')
                    ->label(__('admin.customers.fields.orders_count'))
                    ->counts('orders')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('total_spent')
                    ->label(__('admin.customers.fields.total_spent'))
                    ->state(fn (Customer $record): int => (int) $record->orders()
                        ->whereIn('status', [OrderStatus::Delivered, OrderStatus::Shipped, OrderStatus::Confirmed, OrderStatus::Prepared])
                        ->sum('total'))
                    ->formatStateUsing(fn (int $state): string => Money::format($state))
                    ->alignEnd(),

                IconColumn::make('email_verified_at')
                    ->label(__('admin.customers.fields.verified'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading(__('admin.customers.empty_heading'))
            ->emptyStateDescription(__('admin.customers.empty_description'));
    }
}
