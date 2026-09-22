<?php

namespace App\Filament\Resources\Cuts\Pages;

use App\Filament\Resources\Cuts\CutResource;
use App\Models\Cut;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCut extends EditRecord
{
    protected static string $resource = CutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Un terme employé par un produit ne se supprime pas : la fiche
            // resterait avec une coupe ou un lavage introuvable.
            DeleteAction::make()
                ->before(function (DeleteAction $action): void {
                    /** @var Cut $record */
                    $record = $this->getRecord();

                    if (! $record->isUsed()) {
                        return;
                    }

                    Notification::make()
                        ->danger()
                        ->title(__('admin.cuts.in_use', ['count' => $record->products()->count()]))
                        ->send();

                    $action->cancel();
                }),
        ];
    }
}
