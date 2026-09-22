<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Models\Article;
use App\Models\Product;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

/**
 * Éditeur d'article par blocs.
 *
 * « En bref » vient en premier et reste obligatoire : c'est la réponse
 * directe que reprennent les moteurs génératifs, et l'article ne vaut rien
 * sans elle.
 */
class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.articles.sections.identity'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('title')
                        ->label(__('admin.articles.fields.title'))
                        ->required()
                        ->maxLength(190)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, callable $set, ?Article $record): void {
                            if ($record === null) {
                                $set('slug', Str::slug((string) $state));
                            }
                        }),

                    TextInput::make('slug')
                        ->label(__('admin.articles.fields.slug'))
                        ->helperText(__('admin.articles.fields.slug_hint'))
                        ->required()
                        ->maxLength(190)
                        ->alphaDash()
                        ->unique(ignoreRecord: true),

                    Textarea::make('excerpt')
                        ->label(__('admin.articles.fields.excerpt'))
                        ->helperText(__('admin.articles.fields.excerpt_hint'))
                        ->required()
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),

                    Select::make('author_id')
                        ->label(__('admin.articles.fields.author'))
                        ->relationship('author', 'name')
                        ->searchable()
                        ->preload()
                        ->native(false),

                    DateTimePicker::make('published_at')
                        ->label(__('admin.articles.fields.published_at'))
                        ->helperText(__('admin.articles.fields.published_at_hint'))
                        ->seconds(false),

                    TextInput::make('position')
                        ->label(__('admin.common.position'))
                        ->numeric()
                        ->default(100),
                ]),

            Section::make(__('admin.articles.sections.body'))
                ->columnSpanFull()
                ->schema([
                    Repeater::make('content_blocks')
                        ->label(__('admin.articles.fields.blocks'))
                        ->helperText(__('admin.articles.fields.blocks_hint'))
                        ->schema([
                            TextInput::make('title')
                                ->label(__('admin.articles.fields.block_title'))
                                ->required()
                                ->maxLength(190),
                            Textarea::make('body')
                                ->label(__('admin.articles.fields.block_body'))
                                ->rows(6)
                                ->maxLength(4000),
                        ])
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->collapsible()
                        ->collapsed()
                        ->reorderable()
                        ->defaultItems(0)
                        ->columnSpanFull(),

                    Repeater::make('faq')
                        ->label(__('admin.articles.fields.faq'))
                        ->schema([
                            TextInput::make('question')->label(__('admin.collections.fields.question'))->required()->maxLength(200),
                            Textarea::make('answer')->label(__('admin.collections.fields.answer'))->required()->rows(3)->maxLength(1200),
                        ])
                        ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                        ->collapsible()
                        ->collapsed()
                        ->defaultItems(0)
                        ->columnSpanFull(),
                ]),

            Section::make(__('admin.articles.sections.products'))
                ->description(__('admin.articles.sections.products_hint'))
                ->columnSpanFull()
                ->schema([
                    Select::make('products')
                        ->label(__('admin.articles.fields.products'))
                        ->relationship('products', 'title')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->maxItems(3)
                        ->getOptionLabelFromRecordUsing(fn (Product $record): string => "{$record->title} · {$record->gender->getLabel()}"),
                ]),

            Section::make(__('admin.articles.fields.cover'))
                ->columnSpanFull()
                ->schema([
                    SpatieMediaLibraryFileUpload::make('cover')
                        ->label(__('admin.articles.fields.cover'))
                        ->collection(Article::MEDIA_COVER)
                        // Sans cela, Filament suit FILESYSTEM_DISK et écrit hors du web.
                        ->disk(config('media-library.disk_name'))
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatios(['16:9'])
                        ->maxSize(8192),
                ]),

            Section::make(__('admin.common.seo'))
                ->columns(2)
                ->columnSpanFull()
                ->collapsed()
                ->schema([
                    TextInput::make('meta_title')->label(__('admin.common.meta_title'))->maxLength(190),
                    TextInput::make('meta_description')->label(__('admin.common.meta_description'))->maxLength(320),
                ]),
        ]);
    }
}
