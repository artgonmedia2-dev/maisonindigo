<?php

namespace App\Filament\Resources\Authors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuthorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.authors.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('role')
                    ->label(__('admin.authors.fields.role'))
                    ->color('gray'),

                TextColumn::make('articles_count')
                    ->label(__('admin.articles.plural'))
                    ->counts('articles'),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading(__('admin.authors.empty_heading'));
    }
}
