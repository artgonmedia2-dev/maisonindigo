<?php

namespace App\Filament\Resources\Articles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->columns([
                TextColumn::make('title')
                    ->label(__('admin.articles.fields.title'))
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->limit(60),

                TextColumn::make('author.name')
                    ->label(__('admin.articles.fields.author'))
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label(__('admin.articles.fields.published_at'))
                    ->date('d/m/Y')
                    ->placeholder(__('admin.articles.draft'))
                    ->sortable(),

                TextColumn::make('products_count')
                    ->label(__('admin.articles.fields.products'))
                    ->counts('products'),
            ])
            ->filters([
                TernaryFilter::make('published')
                    ->label(__('admin.articles.fields.published_at'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('published_at'),
                        false: fn (Builder $query) => $query->whereNull('published_at'),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading(__('admin.articles.empty_heading'))
            ->emptyStateDescription(__('admin.articles.empty_description'));
    }
}
