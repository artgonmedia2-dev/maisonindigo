<?php

namespace App\Filament\Resources\Cuts;

use App\Filament\Resources\Cuts\Pages\CreateCut;
use App\Filament\Resources\Cuts\Pages\EditCut;
use App\Filament\Resources\Cuts\Pages\ListCuts;
use App\Filament\Resources\Cuts\Schemas\CutForm;
use App\Filament\Resources\Cuts\Tables\CutsTable;
use App\Models\Cut;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CutResource extends Resource
{
    protected static ?string $model = Cut::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScissors;

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.cuts.plural');
    }

    public static function getModelLabel(): string
    {
        return __('admin.cuts.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.cuts.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return CutForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CutsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('products');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCuts::route('/'),
            'create' => CreateCut::route('/create'),
            'edit' => EditCut::route('/{record}/edit'),
        ];
    }
}
