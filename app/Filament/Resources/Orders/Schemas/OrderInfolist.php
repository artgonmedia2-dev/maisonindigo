<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use App\Support\Money;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make(__('admin.orders.sections.summary'))
                    ->columnSpan(2)
                    ->columns(3)
                    ->schema([
                        TextEntry::make('number')->label(__('admin.orders.fields.number'))->weight('bold'),
                        TextEntry::make('status')->label(__('admin.orders.fields.status'))->badge(),
                        TextEntry::make('payment_method')->label(__('admin.orders.fields.payment_method'))->badge(),
                        TextEntry::make('created_at')->label(__('admin.orders.fields.created_at'))->dateTime('d/m/Y H:i'),
                        TextEntry::make('confirmed_at')->label(__('admin.orders.fields.confirmed_at'))->dateTime('d/m/Y H:i')->placeholder('—'),
                        TextEntry::make('whatsapp_status')->label(__('admin.orders.fields.whatsapp_status'))->placeholder('—'),
                    ]),

                Section::make(__('admin.orders.sections.amounts'))
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('subtotal')->label(__('admin.orders.fields.subtotal'))->formatStateUsing(fn (int $state): string => Money::format($state)),
                        TextEntry::make('discount_total')->label(__('admin.orders.fields.discount_total'))->formatStateUsing(fn (int $state): string => Money::format($state)),
                        TextEntry::make('shipping_total')->label(__('admin.orders.fields.shipping_total'))->formatStateUsing(fn (int $state): string => Money::format($state)),
                        TextEntry::make('total')->label(__('admin.orders.fields.total'))->weight('bold')->formatStateUsing(fn (int $state): string => Money::format($state)),
                    ]),

                Section::make(__('admin.orders.sections.customer'))
                    ->columnSpan(3)
                    ->columns(3)
                    ->schema([
                        TextEntry::make('address_name')->label(__('admin.orders.fields.name'))->state(fn (Order $record): string => (string) data_get($record->shipping_address, 'name')),
                        TextEntry::make('address_phone')->label(__('admin.orders.fields.phone'))->state(fn (Order $record): string => (string) data_get($record->shipping_address, 'phone'))->copyable(),
                        TextEntry::make('address_email')->label(__('admin.orders.fields.email'))->state(fn (Order $record): ?string => data_get($record->shipping_address, 'email'))->placeholder('—'),
                        TextEntry::make('address_lines')->label(__('admin.orders.fields.address'))->columnSpan(2)->state(fn (Order $record): string => implode(', ', array_filter([
                            data_get($record->shipping_address, 'line1'),
                            data_get($record->shipping_address, 'line2'),
                        ]))),
                        TextEntry::make('address_city')->label(__('admin.orders.fields.city'))->state(fn (Order $record): string => trim(implode(' · ', array_filter([
                            data_get($record->shipping_address, 'city'),
                            data_get($record->shipping_address, 'zone'),
                        ])))),
                        TextEntry::make('customer_notes')->label(__('admin.orders.fields.customer_notes'))->columnSpan(3)->placeholder('—'),
                    ]),

                Section::make(__('admin.orders.sections.items'))
                    ->columnSpan(3)
                    ->schema([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->schema([
                                Grid::make(5)->schema([
                                    TextEntry::make('title')->label(__('admin.orders.fields.item'))->columnSpan(2),
                                    TextEntry::make('size_length')->label(__('admin.orders.fields.size'))->state(fn ($record): string => "{$record->size} / {$record->length}"),
                                    TextEntry::make('qty')->label(__('admin.orders.fields.qty')),
                                    TextEntry::make('total')->label(__('admin.orders.fields.total'))->formatStateUsing(fn (int $state): string => Money::format($state)),
                                ]),
                            ]),
                    ]),

                Section::make(__('admin.orders.sections.history'))
                    ->columnSpan(3)
                    ->collapsed()
                    ->schema([
                        RepeatableEntry::make('statusHistories')
                            ->hiddenLabel()
                            ->schema([
                                Grid::make(4)->schema([
                                    TextEntry::make('created_at')->label(__('admin.orders.fields.date'))->dateTime('d/m/Y H:i'),
                                    TextEntry::make('to_status')->label(__('admin.orders.fields.status'))->badge(),
                                    TextEntry::make('admin.name')->label(__('admin.orders.fields.by'))->placeholder(__('admin.orders.by_shop')),
                                    TextEntry::make('comment')->label(__('admin.orders.comment'))->placeholder('—'),
                                ]),
                            ]),
                    ]),
            ]);
    }
}
