<?php

namespace App\Filament\Resources\Discounts\Schemas;

use App\Enums\DiscountType;
use App\Filament\Support\MoneyField;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class DiscountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label(__('admin.discounts.fields.name'))
                            ->required()
                            ->maxLength(190),

                        TextInput::make('code')
                            ->label(__('admin.discounts.fields.code'))
                            ->helperText(__('admin.discounts.fields.code_hint'))
                            ->unique(ignoreRecord: true)
                            ->maxLength(30)
                            ->dehydrateStateUsing(fn (?string $state): ?string => blank($state) ? null : mb_strtoupper(trim($state))),

                        Select::make('type')
                            ->label(__('admin.discounts.fields.type'))
                            ->options(DiscountType::class)
                            ->default(DiscountType::Percent)
                            ->required()
                            ->live()
                            ->native(false),

                        TextInput::make('value')
                            ->label(__('admin.discounts.fields.value_percent'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->suffix('%')
                            ->required()
                            ->visible(fn (Get $get): bool => $get('type') !== DiscountType::Fixed->value),

                        MoneyField::make('value', __('admin.discounts.fields.value_fixed'))
                            ->required()
                            ->visible(fn (Get $get): bool => $get('type') === DiscountType::Fixed->value),
                    ]),

                Section::make(__('admin.discounts.fields.rules'))
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('rules.min_qty')
                            ->label(__('admin.discounts.fields.rules_min_qty'))
                            ->numeric()
                            ->minValue(1)
                            ->default(1),

                        MoneyField::make('rules.min_subtotal', __('admin.discounts.fields.rules_min_subtotal')),
                    ]),

                Section::make()
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        DateTimePicker::make('starts_at')
                            ->label(__('admin.discounts.fields.starts_at'))
                            ->seconds(false)
                            ->native(false),

                        DateTimePicker::make('ends_at')
                            ->label(__('admin.discounts.fields.ends_at'))
                            ->seconds(false)
                            ->native(false)
                            ->after('starts_at'),

                        TextInput::make('usage_limit')
                            ->label(__('admin.discounts.fields.usage_limit'))
                            ->numeric()
                            ->minValue(1),

                        Toggle::make('active')
                            ->label(__('admin.discounts.fields.active'))
                            ->default(true),
                    ]),
            ]);
    }
}
