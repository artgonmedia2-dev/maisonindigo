<?php

namespace App\Filament\Resources\Reviews\Schemas;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Saisie d'un avis.
 *
 * Aucun champ « vérifié » : la mention se déduit de la commande rattachée, et
 * seules les commandes livrées la produisent. C'est la seule façon d'éviter
 * qu'un badge de confiance finisse par ne rien garantir.
 */
class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.reviews.sections.review'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('author_name')
                        ->label(__('admin.reviews.fields.author_name'))
                        ->helperText(__('admin.reviews.fields.author_name_hint'))
                        ->required()
                        ->maxLength(120),

                    TextInput::make('city')
                        ->label(__('admin.reviews.fields.city'))
                        ->maxLength(80),

                    Select::make('rating')
                        ->label(__('admin.reviews.fields.rating'))
                        ->options([5 => '5', 4 => '4', 3 => '3', 2 => '2', 1 => '1'])
                        ->default(5)
                        ->required()
                        ->native(false),

                    TextInput::make('size_bought')
                        ->label(__('admin.reviews.fields.size_bought'))
                        ->helperText(__('admin.reviews.fields.size_bought_hint'))
                        ->maxLength(20),

                    Textarea::make('body')
                        ->label(__('admin.reviews.fields.body'))
                        ->helperText(__('admin.reviews.fields.body_hint'))
                        ->required()
                        ->rows(4)
                        ->maxLength(600)
                        ->columnSpanFull(),
                ]),

            Section::make(__('admin.reviews.sections.proof'))
                ->description(__('admin.reviews.sections.proof_hint'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Select::make('order_id')
                        ->label(__('admin.reviews.fields.order'))
                        ->helperText(__('admin.reviews.fields.order_hint'))
                        // Seules les commandes livrées sont proposées : elles
                        // seules produisent la mention « Achat vérifié ».
                        ->options(fn (): array => Order::query()
                            ->where('status', OrderStatus::Delivered)
                            ->orderByDesc('id')
                            ->limit(200)
                            ->pluck('number', 'id')
                            ->all())
                        ->searchable()
                        ->native(false),

                    Select::make('product_id')
                        ->label(__('admin.reviews.fields.product'))
                        ->relationship('product', 'title')
                        ->getOptionLabelFromRecordUsing(fn (Product $record): string => "{$record->title} · {$record->gender->getLabel()}")
                        ->searchable()
                        ->preload()
                        ->native(false),

                    DateTimePicker::make('published_at')
                        ->label(__('admin.reviews.fields.published_at'))
                        ->helperText(__('admin.reviews.fields.published_at_hint'))
                        ->seconds(false),

                    TextInput::make('position')
                        ->label(__('admin.common.position'))
                        ->numeric()
                        ->default(100),
                ]),
        ]);
    }
}
