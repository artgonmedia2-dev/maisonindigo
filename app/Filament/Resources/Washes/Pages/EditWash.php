<?php

namespace App\Filament\Resources\Washes\Pages;

use App\Filament\Resources\Washes\WashResource;
use App\Models\Wash;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditWash extends EditRecord
{
    protected static string $resource = WashResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Un terme employé par un produit ne se supprime pas : la fiche
            // resterait avec une coupe ou un lavage introuvable.
            DeleteAction::make()
                ->before(function (DeleteAction $action): void {
                    /** @var Wash $record */
                    $record = $this->getRecord();

                    if (! $record->isUsed()) {
                        return;
                    }

                    Notification::make()
                        ->danger()
                        ->title(__('admin.washes.in_use', ['count' => $record->products()->count()]))
                        ->send();

                    $action->cancel();
                }),
        ];
    }
}
