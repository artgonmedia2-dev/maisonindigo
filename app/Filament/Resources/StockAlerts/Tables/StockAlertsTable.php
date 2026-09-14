<?php

namespace App\Filament\Resources\StockAlerts\Tables;

use App\Models\StockAlert;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class StockAlertsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('variant.product.title')
                    ->label(__('admin.stock_alerts.fields.product'))
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('variant.sku')
                    ->label(__('admin.stock_alerts.fields.variant'))
                    ->formatStateUsing(fn (string $state, StockAlert $record): string => $record->variant->label())
                    ->description(fn (StockAlert $record): string => (string) $record->variant->sku),

                TextColumn::make('variant.stock')
                    ->label(__('admin.stock_alerts.fields.stock'))
                    ->badge()
                    ->alignCenter()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger'),

                TextColumn::make('email')
                    ->label(__('admin.stock_alerts.fields.email'))
                    ->searchable()
                    ->copyable()
                    ->placeholder('—'),

                TextColumn::make('phone')
                    ->label(__('admin.stock_alerts.fields.phone'))
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->since()
                    ->sortable(),

                TextColumn::make('notified_at')
                    ->label(__('admin.stock_alerts.fields.notified_at'))
                    ->dateTime('d/m/Y H:i')
                    ->placeholder(__('admin.stock_alerts.fields.pending')),
            ])
            ->filters([
                TernaryFilter::make('notified_at')
                    ->label(__('admin.stock_alerts.fields.notified_at'))
                    ->nullable()
                    ->placeholder('—')
                    ->trueLabel(__('admin.stock_alerts.fields.notified_at'))
                    ->falseLabel(__('admin.stock_alerts.fields.pending'))
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('notified_at'),
                        false: fn (Builder $query): Builder => $query->whereNull('notified_at'),
                        blank: fn (Builder $query): Builder => $query,
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('marquer')
                        ->label(__('admin.stock_alerts.mark_notified'))
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $count = 0;

                            /** @var StockAlert $record */
                            foreach ($records as $record) {
                                if ($record->notified_at === null) {
                                    $record->forceFill(['notified_at' => now()])->save();
                                    $count++;
                                }
                            }

                            Notification::make()->title(__('admin.stock_alerts.marked', ['count' => $count]))->success()->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading(__('admin.stock_alerts.empty_heading'))
            ->emptyStateDescription(__('admin.stock_alerts.empty_description'));
    }
}
