<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.customers.sections.identity'))
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label(__('admin.customers.fields.name'))
                            ->required()
                            ->maxLength(190),

                        TextInput::make('email')
                            ->label(__('admin.customers.fields.email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(190),

                        TextInput::make('phone')
                            ->label(__('admin.customers.fields.phone'))
                            ->tel()
                            ->maxLength(20),
                    ]),
            ]);
    }
}
