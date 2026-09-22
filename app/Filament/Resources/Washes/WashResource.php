<?php

namespace App\Filament\Resources\Washes;

use App\Filament\Resources\Washes\Pages\CreateWash;
use App\Filament\Resources\Washes\Pages\EditWash;
use App\Filament\Resources\Washes\Pages\ListWashes;
use App\Filament\Resources\Washes\Schemas\WashForm;
use App\Filament\Resources\Washes\Tables\WashesTable;
use App\Models\Wash;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WashResource extends Resource
{
    protected static ?string $model = Wash::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.washes.plural');
    }

    public static function getModelLabel(): string
    {
        return __('admin.washes.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.washes.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return WashForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WashesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('products');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWashes::route('/'),
            'create' => CreateWash::route('/create'),
            'edit' => EditWash::route('/{record}/edit'),
        ];
    }
}
