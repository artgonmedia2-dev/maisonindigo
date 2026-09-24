<?php

namespace App\Filament\Resources\Reviews\Tables;

use App\Models\Review;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->columns([
                TextColumn::make('author_name')
                    ->label(__('admin.reviews.fields.author_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn (Review $record): ?string => $record->city),

                TextColumn::make('rating')
                    ->label(__('admin.reviews.fields.rating'))
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => "{$state}/5")
                    ->color(fn (int $state): string => $state >= 4 ? 'success' : ($state === 3 ? 'warning' : 'danger')),

                TextColumn::make('body')
                    ->label(__('admin.reviews.fields.body'))
                    ->limit(60)
                    ->color('gray'),

                IconColumn::make('verified')
                    ->label(__('admin.reviews.fields.verified'))
                    ->boolean()
                    ->state(fn (Review $record): bool => $record->isVerified()),

                TextColumn::make('published_at')
                    ->label(__('admin.reviews.fields.published_at'))
                    ->date('d/m/Y')
                    ->placeholder(__('admin.reviews.draft'))
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('published')
                    ->label(__('admin.reviews.fields.published_at'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('published_at'),
                        false: fn (Builder $query) => $query->whereNull('published_at'),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading(__('admin.reviews.empty_heading'))
            ->emptyStateDescription(__('admin.reviews.empty_description'));
    }
}
