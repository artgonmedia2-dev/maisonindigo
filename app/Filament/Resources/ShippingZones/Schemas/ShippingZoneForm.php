<?php

namespace App\Filament\Resources\ShippingZones\Schemas;

use App\Filament\Support\MoneyField;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ShippingZoneForm
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
                            ->label(__('admin.shipping.fields.name'))
                            ->required()
                            ->maxLength(190),

                        TextInput::make('delay')
                            ->label(__('admin.shipping.fields.delay'))
                            ->helperText(__('admin.shipping.fields.delay_hint'))
                            ->required()
                            ->datalist(['24 h', '24 à 48 h', '48 h', '48 à 72 h'])
                            ->maxLength(60),

                        TagsInput::make('cities')
                            ->label(__('admin.shipping.fields.cities'))
                            ->helperText(__('admin.shipping.fields.cities_hint'))
                            ->columnSpanFull()
                            ->suggestions(['Casablanca', 'Rabat', 'Salé', 'Tanger', 'Marrakech', 'Fès', 'Meknès', 'Agadir', 'Oujda', 'Nador', 'Tétouan', 'Kénitra']),

                        TextInput::make('position')
                            ->label(__('admin.common.position'))
                            ->numeric()
                            ->minValue(0)
                            ->default(0),

                        Toggle::make('is_active')
                            ->label(__('admin.shipping.fields.is_active'))
                            ->default(true),
                    ]),

                Section::make(__('admin.shipping.fields.rate'))
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        MoneyField::make('price', __('admin.shipping.fields.price'))
                            ->required()
                            ->default(3500),

                        MoneyField::make('free_threshold', __('admin.shipping.fields.free_threshold'))
                            ->helperText(__('admin.shipping.fields.free_threshold_hint'))
                            ->default(60000),
                    ]),
            ]);
    }
}
