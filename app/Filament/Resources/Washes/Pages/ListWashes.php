<?php

namespace App\Filament\Resources\Washes\Pages;

use App\Filament\Resources\Washes\WashResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWashes extends ListRecords
{
    protected static string $resource = WashResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
