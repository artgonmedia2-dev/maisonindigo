<?php

namespace App\Filament\Resources\SizeCharts\RelationManagers;

use App\Models\ProductVariant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RowsRelationManager extends RelationManager
{
    protected static string $relationship = 'rows';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.size_charts.rows.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Select::make('size')
                    ->label(__('admin.size_charts.rows.size'))
                    ->options(self::sizeOptions())
                    ->required()
                    ->native(false),

                self::centimetres('waist_cm', __('admin.size_charts.rows.waist_cm')),
                self::centimetres('hips_cm', __('admin.size_charts.rows.hips_cm')),
                self::centimetres('thigh_cm', __('admin.size_charts.rows.thigh_cm')),
                self::centimetres('inseam_30', __('admin.size_charts.rows.inseam_30')),
                self::centimetres('inseam_32', __('admin.size_charts.rows.inseam_32')),
                self::centimetres('inseam_34', __('admin.size_charts.rows.inseam_34')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('size')
            ->defaultSort('size')
            ->defaultPaginationPageOption(25)
            ->description(__('admin.size_charts.rows.hint'))
            ->columns([
                TextColumn::make('size')->label(__('admin.size_charts.rows.size'))->sortable(),
                TextColumn::make('waist_cm')->label(__('admin.size_charts.rows.waist_cm')),
                TextColumn::make('hips_cm')->label(__('admin.size_charts.rows.hips_cm')),
                TextColumn::make('thigh_cm')->label(__('admin.size_charts.rows.thigh_cm')),
                TextColumn::make('inseam_30')->label(__('admin.size_charts.rows.inseam_30'))->toggleable(),
                TextColumn::make('inseam_32')->label(__('admin.size_charts.rows.inseam_32'))->toggleable(),
                TextColumn::make('inseam_34')->label(__('admin.size_charts.rows.inseam_34'))->toggleable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @return array<int, string>
     */
    private static function sizeOptions(): array
    {
        $options = [];

        foreach (ProductVariant::SIZES as $size) {
            $options[(string) $size] = (string) $size;
        }

        return $options;
    }

    private static function centimetres(string $name, string $label): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->numeric()
            ->minValue(10)
            ->maxValue(200)
            ->step(0.1)
            ->suffix('cm')
            ->required();
    }
}
