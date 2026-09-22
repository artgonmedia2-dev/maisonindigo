<?php

namespace App\Filament\Resources\Collections\Schemas;

use App\Enums\CollectionType;
use App\Enums\HomeSlot;
use App\Models\Collection;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CollectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->label(__('admin.collections.fields.title'))
                            ->required()
                            ->maxLength(190),

                        TextInput::make('slug')
                            ->label(__('admin.common.slug'))
                            ->helperText(__('admin.common.slug_hint'))
                            ->unique(ignoreRecord: true)
                            ->maxLength(190),

                        Textarea::make('description')
                            ->label(__('admin.collections.fields.description'))
                            ->rows(3)
                            ->columnSpanFull()
                            ->maxLength(1000),

                        Select::make('type')
                            ->label(__('admin.collections.fields.type'))
                            ->options(CollectionType::class)
                            ->default(CollectionType::Manual)
                            ->required()
                            ->live()
                            ->native(false),

                        TextInput::make('position')
                            ->label(__('admin.common.position'))
                            ->numeric()
                            ->minValue(0)
                            ->default(0),

                        Toggle::make('is_visible')
                            ->label(__('admin.common.visible'))
                            ->default(true),
                    ]),

                Section::make(__('admin.collections.fields.products'))
                    ->columnSpanFull()
                    ->visible(fn (Get $get): bool => $get('type') !== CollectionType::Rule->value)
                    ->schema([
                        Select::make('products')
                            ->label(__('admin.collections.fields.products'))
                            ->relationship('products', 'title')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                    ]),

                Section::make(__('admin.collections.fields.rules'))
                    ->description(__('admin.collections.fields.rules_hint'))
                    ->columnSpanFull()
                    ->visible(fn (Get $get): bool => $get('type') === CollectionType::Rule->value)
                    ->schema([
                        KeyValue::make('rules')
                            ->label(__('admin.collections.fields.rules'))
                            ->keyLabel('Critère')
                            ->valueLabel('Valeur'),
                    ]),

                Section::make(__('admin.collections.fields.cover'))
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('cover')
                            ->label(__('admin.collections.fields.cover'))
                            ->helperText(__('admin.collections.fields.cover_hint'))
                            ->collection(Collection::MEDIA_COVER)
                            // Sans cela, Filament suit FILESYSTEM_DISK et écrit hors du web.
                            ->disk(config('media-library.disk_name'))
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['4:5', '16:9'])
                            ->maxSize(8192)
                            ->columnSpanFull(),

                        Select::make('home_slot')
                            ->label(__('admin.collections.fields.home_slot'))
                            ->helperText(__('admin.collections.fields.home_slot_hint'))
                            ->options(HomeSlot::class)
                            ->native(false)
                            // Un seul emplacement par carte : sinon l'accueil
                            // afficherait deux fois la même collection.
                            ->unique(ignoreRecord: true)
                            ->validationMessages(['unique' => __('admin.collections.home_slot_taken')]),

                        Select::make('home_badge')
                            ->label(__('admin.collections.fields.home_badge'))
                            ->helperText(__('admin.collections.fields.home_badge_hint'))
                            ->options(self::badgeOptions())
                            ->native(false),
                    ]),

                Section::make(__('admin.common.seo'))
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title')
                            ->label(__('admin.common.meta_title'))
                            ->maxLength(190),

                        Textarea::make('meta_description')
                            ->label(__('admin.common.meta_description'))
                            ->rows(2)
                            ->maxLength(320),
                    ]),
            ]);
    }

    /**
     * Les trois badges de la charte ; le champ se vide pour n'en afficher aucun.
     *
     * @return array<string, string>
     */
    private static function badgeOptions(): array
    {
        return [
            'new' => __('admin.settings.badges.new'),
            'limited' => __('admin.settings.badges.limited'),
            'atelier' => __('admin.settings.badges.atelier'),
        ];
    }
}
