<?php

namespace App\Filament\Resources\Members\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('email')
                            ->label(__('admin.members.fields.email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(190),

                        TextInput::make('phone')
                            ->label(__('admin.members.fields.phone'))
                            ->tel()
                            ->maxLength(20),

                        TextInput::make('source')
                            ->label(__('admin.members.fields.source'))
                            ->datalist(['footer', 'checkout', 'instagram', 'import'])
                            ->maxLength(60),

                        DateTimePicker::make('consent_at')
                            ->label(__('admin.members.fields.consent_at'))
                            ->seconds(false)
                            ->native(false)
                            ->default(now()),
                    ]),
            ]);
    }
}
