<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\Gender;
use App\Enums\ProductStatus;
use App\Filament\Support\MoneyField;
use App\Models\Cut;
use App\Models\Product;
use App\Models\SizeChart;
use App\Models\Wash;
use App\Support\CatalogTerms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make(__('admin.products.sections.identity'))->schema(self::identity()),
                        Tab::make(__('admin.products.sections.denim'))->schema(self::denim()),
                        Tab::make(__('admin.products.sections.photos'))->schema(self::photos()),
                        Tab::make(__('admin.common.seo'))->schema(self::seo()),
                    ]),
            ]);
    }

    /**
     * @return array<int, mixed>
     */
    private static function identity(): array
    {
        return [
            Section::make(__('admin.products.sections.identity'))
                ->description(__('admin.products.sections.identity_hint'))
                ->columns(3)
                ->schema([
                    Select::make('gender')
                        ->label(__('admin.products.fields.gender'))
                        ->options(Gender::class)
                        ->required()
                        ->live()
                        ->native(false)
                        ->afterStateUpdated(function (callable $set, Get $get): void {
                            // La coupe change de liste avec le genre : on ne garde que si elle existe encore.
                            if (! array_key_exists((string) $get('cut'), self::cutOptions($get('gender')))) {
                                $set('cut', null);
                            }

                            self::refreshTitle($set, $get);
                        }),

                    Select::make('cut')
                        ->label(__('admin.products.fields.cut'))
                        ->options(fn (Get $get): array => self::cutOptions($get('gender')))
                        ->required()
                        ->live()
                        ->native(false)
                        ->afterStateUpdated(fn (callable $set, Get $get) => self::refreshTitle($set, $get)),

                    Select::make('wash')
                        ->label(__('admin.products.fields.wash'))
                        ->options(fn (): array => self::washOptions())
                        ->required()
                        ->live()
                        ->native(false)
                        // Un produit = 1 coupe x 1 lavage x 1 genre : le trio est unique.
                        ->unique(
                            table: Product::class,
                            column: 'wash',
                            ignoreRecord: true,
                            modifyRuleUsing: fn (Unique $rule, Get $get): Unique => $rule
                                ->where('gender', $get('gender'))
                                ->where('cut', $get('cut')),
                        )
                        ->validationMessages(['unique' => __('admin.products.duplicate')])
                        ->afterStateUpdated(fn (callable $set, Get $get) => self::refreshTitle($set, $get)),

                    TextInput::make('title')
                        ->label(__('admin.products.fields.title'))
                        ->helperText(__('admin.products.fields.title_hint'))
                        ->columnSpan(2)
                        ->required()
                        ->maxLength(120)
                        ->placeholder(fn (Get $get): string => Product::composeTitle($get('cut'), $get('wash'))),

                    Select::make('status')
                        ->label(__('admin.products.fields.status'))
                        ->options(ProductStatus::class)
                        ->default(ProductStatus::Draft)
                        ->required()
                        ->native(false),

                    Textarea::make('description')
                        ->label(__('admin.products.fields.description'))
                        ->rows(4)
                        ->columnSpanFull()
                        ->maxLength(2000),
                ]),

            Section::make(__('admin.products.sections.price'))
                ->columns(3)
                ->schema([
                    MoneyField::make('price', __('admin.products.fields.price'))->required(),
                    MoneyField::make('compare_at_price', __('admin.products.fields.compare_at_price'))
                        ->helperText(__('admin.products.fields.compare_at_price_hint')),
                ]),

            Section::make(__('admin.products.sections.flags'))
                ->columns(3)
                ->schema([
                    Toggle::make('is_new')->label(__('admin.products.fields.is_new')),
                    Toggle::make('is_featured')->label(__('admin.products.fields.is_featured')),
                    Toggle::make('is_atelier')->label(__('admin.products.fields.is_atelier')),
                ]),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private static function denim(): array
    {
        return [
            Section::make(__('admin.products.sections.denim'))
                ->description(__('admin.products.sections.denim_hint'))
                ->columns(3)
                ->schema([
                    TextInput::make('fabric_origin')
                        ->label(__('admin.products.fields.fabric_origin'))
                        ->datalist(['Denim japonais', 'Denim turc', 'Denim italien', 'Denim marocain'])
                        ->maxLength(190),

                    TextInput::make('weight_oz')
                        ->label(__('admin.products.fields.weight_oz'))
                        ->numeric()
                        ->minValue(8)
                        ->maxValue(20)
                        ->step(0.5),

                    TextInput::make('composition')
                        ->label(__('admin.products.fields.composition'))
                        ->datalist(['100 % coton', '98 % coton, 2 % élasthanne', '99 % coton, 1 % élasthanne'])
                        ->maxLength(190),
                ]),

            Section::make(__('admin.products.sections.sizing'))
                ->columns(3)
                ->schema([
                    TextInput::make('model_height_cm')
                        ->label(__('admin.products.fields.model_height_cm'))
                        ->numeric()
                        ->minValue(140)
                        ->maxValue(210),

                    TextInput::make('model_size')
                        ->label(__('admin.products.fields.model_size'))
                        ->placeholder('32/32')
                        ->maxLength(10),

                    Select::make('size_chart_id')
                        ->label(__('admin.products.fields.size_chart'))
                        ->options(fn (): array => SizeChart::query()->pluck('title', 'id')->all())
                        ->searchable()
                        ->preload()
                        ->native(false),

                    Textarea::make('size_advice')
                        ->label(__('admin.products.fields.size_advice'))
                        ->rows(2)
                        ->columnSpanFull()
                        ->default('Entre deux tailles, choisissez la plus petite : le tissu se détend légèrement.')
                        ->maxLength(500),
                ]),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private static function photos(): array
    {
        return [
            Section::make(__('admin.products.sections.photos'))
                ->description(__('admin.products.sections.photos_hint'))
                ->schema([
                    SpatieMediaLibraryFileUpload::make('gallery')
                        ->label(__('admin.products.fields.gallery'))
                        ->collection(Product::MEDIA_GALLERY)
                        // Sans cela, Filament suit FILESYSTEM_DISK et écrit hors du web.
                        ->disk(config('media-library.disk_name'))
                        ->multiple()
                        ->reorderable()
                        ->appendFiles()
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatios(['4:5'])
                        ->maxFiles(6)
                        ->maxSize(8192)
                        ->panelLayout('grid')
                        ->columnSpanFull(),
                ]),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private static function seo(): array
    {
        return [
            Section::make(__('admin.common.seo'))
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('slug')
                            ->label(__('admin.common.slug'))
                            ->helperText(__('admin.common.slug_hint'))
                            ->unique(ignoreRecord: true)
                            ->maxLength(190),

                        TextInput::make('meta_title')
                            ->label(__('admin.common.meta_title'))
                            ->maxLength(190),
                    ]),

                    Textarea::make('meta_description')
                        ->label(__('admin.common.meta_description'))
                        ->rows(3)
                        ->maxLength(320),
                ]),
        ];
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

    /**
     * Le titre suit la coupe et le lavage tant que personne ne l'a réécrit.
     */
    private static function refreshTitle(callable $set, Get $get): void
    {
        if (! Product::isComposedTitle($get('title'))) {
            return;
        }

        $set('title', Product::composeTitle($get('cut'), $get('wash')));
    }

    /**
     * @return array<string, string>
     */
    private static function washOptions(): array
    {
        $options = [];

        foreach (app(CatalogTerms::class)->activeWashes() as $wash) {
            /** @var Wash $wash */
            $options[$wash->slug] = $wash->name;
        }

        return $options;
    }
}
