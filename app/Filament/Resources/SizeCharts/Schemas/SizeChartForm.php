<?php

namespace App\Filament\Resources\SizeCharts\Schemas;

use App\Enums\Cut;
use App\Enums\Gender;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class SizeChartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('gender')
                            ->label(__('admin.size_charts.fields.gender'))
                            ->options(Gender::class)
                            ->required()
                            ->live()
                            ->native(false)
                            ->afterStateUpdated(function (callable $set, Get $get): void {
                                $gender = Gender::tryFrom((string) $get('gender'));
                                $available = $gender === null ? Cut::cases() : Cut::forGender($gender);

                                if (! in_array(Cut::tryFrom((string) $get('cut')), $available, true)) {
                                    $set('cut', null);
                                }
                            }),

                        Select::make('cut')
                            ->label(__('admin.size_charts.fields.cut'))
                            ->options(function (Get $get): array {
                                $gender = $get('gender') instanceof Gender ? $get('gender') : Gender::tryFrom((string) $get('gender'));
                                $cuts = $gender === null ? Cut::cases() : Cut::forGender($gender);
                                $options = [];

                                foreach ($cuts as $cut) {
                                    $options[$cut->value] = $cut->getLabel();
                                }

                                return $options;
                            })
                            ->required()
                            ->native(false),

                        TextInput::make('title')
                            ->label(__('admin.size_charts.fields.title'))
                            ->required()
                            ->maxLength(190),
                    ]),
            ]);
    }
}
