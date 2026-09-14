<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Tailles et stocks d'un produit. « Générer les tailles » crée la grille
 * complète taille × longueur en une fois, sans toucher aux stocks existants.
 */
class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.variants.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('size')
                    ->label(__('admin.variants.fields.size'))
                    ->options(self::sizeOptions(ProductVariant::SIZES))
                    ->required()
                    ->native(false),

                Select::make('length')
                    ->label(__('admin.variants.fields.length'))
                    ->options(self::sizeOptions(ProductVariant::LENGTHS))
                    ->required()
                    ->native(false),

                TextInput::make('stock')
                    ->label(__('admin.variants.fields.stock'))
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required(),

                TextInput::make('low_stock_threshold')
                    ->label(__('admin.variants.fields.low_stock_threshold'))
                    ->numeric()
                    ->minValue(0)
                    ->default(3)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sku')
            ->defaultSort('position')
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('size')
                    ->label(__('admin.variants.fields.size'))
                    ->sortable(),

                TextColumn::make('length')
                    ->label(__('admin.variants.fields.length'))
                    ->sortable(),

                TextColumn::make('sku')
                    ->label(__('admin.variants.fields.sku'))
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('stock')
                    ->label(__('admin.variants.fields.stock'))
                    ->badge()
                    ->sortable()
                    ->color(fn (ProductVariant $record): string => match (true) {
                        $record->stock === 0 => 'danger',
                        $record->isLowStock() => 'warning',
                        default => 'success',
                    }),

                TextColumn::make('low_stock_threshold')
                    ->label(__('admin.variants.fields.low_stock_threshold'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('low_stock')
                    ->label(__('admin.products.filters.low_stock'))
                    ->query(fn (Builder $query): Builder => $query->whereColumn('stock', '<=', 'low_stock_threshold')),
            ])
            ->headerActions([
                $this->generateAction(),
                CreateAction::make()
                    ->mutateDataUsing(fn (array $data): array => $this->withSku($data)),
            ])
            ->recordActions([
                $this->restockAction(),
                EditAction::make()->mutateDataUsing(fn (array $data): array => $this->withSku($data)),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading(__('admin.variants.empty_heading'))
            ->emptyStateDescription(__('admin.variants.empty_description'));
    }

    private function generateAction(): Action
    {
        return Action::make('generer')
            ->label(__('admin.variants.generate'))
            ->icon('heroicon-o-squares-plus')
            ->modalDescription(__('admin.variants.generate_hint'))
            ->schema([
                Select::make('sizes')
                    ->label(__('admin.variants.generate_sizes'))
                    ->multiple()
                    ->options(self::sizeOptions(ProductVariant::SIZES))
                    ->default(range(28, 42, 2))
                    ->required(),

                Select::make('lengths')
                    ->label(__('admin.variants.generate_lengths'))
                    ->multiple()
                    ->options(self::sizeOptions(ProductVariant::LENGTHS))
                    ->default(ProductVariant::LENGTHS)
                    ->required(),

                TextInput::make('stock')
                    ->label(__('admin.variants.generate_stock'))
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
            ])
            ->action(function (array $data): void {
                /** @var Product $product */
                $product = $this->getOwnerRecord();

                $existing = $product->variants()->get()->map(fn (ProductVariant $variant): string => "{$variant->size}-{$variant->length}")->all();
                $position = (int) $product->variants()->max('position');
                $created = 0;

                foreach ($data['sizes'] as $size) {
                    foreach ($data['lengths'] as $length) {
                        if (in_array("{$size}-{$length}", $existing, true)) {
                            continue;
                        }

                        $product->variants()->create([
                            'size' => (int) $size,
                            'length' => (int) $length,
                            'sku' => ProductVariant::buildSku($product, (int) $size, (int) $length),
                            'stock' => (int) $data['stock'],
                            'low_stock_threshold' => 3,
                            'position' => ++$position,
                        ]);

                        $created++;
                    }
                }

                Notification::make()->title(__('admin.variants.generated', ['count' => $created]))->success()->send();
            });
    }

    private function restockAction(): Action
    {
        return Action::make('reassortir')
            ->label(__('admin.variants.restock'))
            ->icon('heroicon-o-plus-circle')
            ->color('gray')
            ->schema([
                TextInput::make('quantity')
                    ->label(__('admin.variants.restock_quantity'))
                    ->numeric()
                    ->minValue(1)
                    ->default(10)
                    ->required(),
            ])
            ->action(function (array $data, ProductVariant $record): void {
                $record->increment('stock', (int) $data['quantity']);

                Notification::make()->title(__('admin.variants.restocked'))->success()->send();
            });
    }

    /**
     * @param  list<int>  $values
     * @return array<int, string>
     */
    private static function sizeOptions(array $values): array
    {
        $options = [];

        foreach ($values as $value) {
            $options[(string) $value] = (string) $value;
        }

        return $options;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withSku(array $data): array
    {
        /** @var Product $product */
        $product = $this->getOwnerRecord();

        $data['sku'] = ProductVariant::buildSku($product, (int) $data['size'], (int) $data['length']);

        return $data;
    }
}
