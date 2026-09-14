<?php

namespace App\Filament\Resources\StockAlerts;

use App\Filament\Resources\StockAlerts\Pages\ListStockAlerts;
use App\Filament\Resources\StockAlerts\Schemas\StockAlertForm;
use App\Filament\Resources\StockAlerts\Tables\StockAlertsTable;
use App\Models\StockAlert;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockAlertResource extends Resource
{
    protected static ?string $model = StockAlert::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.stock_alerts.plural');
    }

    public static function getModelLabel(): string
    {
        return __('admin.stock_alerts.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.stock_alerts.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return StockAlertForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockAlertsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = StockAlert::query()->whereNull('notified_at')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('variant.product');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockAlerts::route('/'),
        ];
    }
}
