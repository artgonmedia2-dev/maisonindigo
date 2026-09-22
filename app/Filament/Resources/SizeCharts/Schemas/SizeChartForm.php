<?php

namespace App\Filament\Resources\SizeCharts\Schemas;

use App\Enums\Gender;
use App\Models\Cut;
use App\Support\CatalogTerms;
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
                                // La coupe retenue doit rester proposée pour ce genre.
                                if (! array_key_exists((string) $get('cut'), self::cutOptions($get('gender')))) {
                                    $set('cut', null);
                                }
                            }),

                        Select::make('cut')
                            ->label(__('admin.size_charts.fields.cut'))
                            ->options(fn (Get $get): array => self::cutOptions($get('gender')))
                            ->required()
                            ->native(false),

                        TextInput::make('title')
                            ->label(__('admin.size_charts.fields.title'))
                            ->required()
                            ->maxLength(190),
                    ]),
            ]);
    }

    /**
     * @return array<string, string>
     */
    private static function cutOptions(mixed $gender): array
    {
        $gender = $gender instanceof Gender ? $gender : Gender::tryFrom((string) $gender);

        $options = [];

        foreach (app(CatalogTerms::class)->activeCuts($gender) as $cut) {
            /** @var Cut $cut */
            $options[$cut->slug] = $cut->name;
        }

        return $options;
    }
}
