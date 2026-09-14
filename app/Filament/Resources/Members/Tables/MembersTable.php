<?php

namespace App\Filament\Resources\Members\Tables;

use App\Models\Member;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('email')
                    ->label(__('admin.members.fields.email'))
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('phone')
                    ->label(__('admin.members.fields.phone'))
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('source')
                    ->label(__('admin.members.fields.source'))
                    ->badge()
                    ->placeholder('—'),

                TextColumn::make('consent_at')
                    ->label(__('admin.members.fields.consent_at'))
                    ->dateTime('d/m/Y')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('source')
                    ->label(__('admin.members.fields.source'))
                    ->options(fn (): array => Member::query()
                        ->whereNotNull('source')
                        ->distinct()
                        ->pluck('source', 'source')
                        ->all()),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading(__('admin.members.empty_heading'))
            ->emptyStateDescription(__('admin.members.empty_description'));
    }
}
