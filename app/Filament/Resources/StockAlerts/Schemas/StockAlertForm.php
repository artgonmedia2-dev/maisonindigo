<?php

namespace App\Filament\Resources\StockAlerts\Schemas;

use Filament\Schemas\Schema;

class StockAlertForm
{
    /**
     * Les demandes « Me prévenir » viennent de la boutique : elles se consultent,
     * elles ne se saisissent pas.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([]);
    }
}
