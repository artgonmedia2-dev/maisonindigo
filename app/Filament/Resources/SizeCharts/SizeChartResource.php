<?php

namespace App\Filament\Resources\SizeCharts;

use App\Filament\Resources\SizeCharts\Pages\CreateSizeChart;
use App\Filament\Resources\SizeCharts\Pages\EditSizeChart;
use App\Filament\Resources\SizeCharts\Pages\ListSizeCharts;
use App\Filament\Resources\SizeCharts\RelationManagers\RowsRelationManager;
use App\Filament\Resources\SizeCharts\Schemas\SizeChartForm;
use App\Filament\Resources\SizeCharts\Tables\SizeChartsTable;
use App\Models\SizeChart;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SizeChartResource extends Resource
{
    protected static ?string $model = SizeChart::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.size_charts.plural');
    }

    public static function getModelLabel(): string
    {
        return __('admin.size_charts.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.size_charts.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return SizeChartForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SizeChartsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RowsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSizeCharts::route('/'),
            'create' => CreateSizeChart::route('/create'),
            'edit' => EditSizeChart::route('/{record}/edit'),
        ];
    }
}
