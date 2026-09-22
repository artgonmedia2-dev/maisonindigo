<?php

namespace App\Filament\Resources\Cuts\Pages;

use App\Filament\Resources\Cuts\CutResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCuts extends ListRecords
{
    protected static string $resource = CutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
