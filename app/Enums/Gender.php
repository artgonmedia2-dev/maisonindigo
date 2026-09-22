<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Gender: string implements HasLabel
{
    case Homme = 'homme';
    case Femme = 'femme';

    public function getLabel(): string
    {
        return __('enums.gender.'.$this->value);
    }

    /**
     * Code utilisé dans le SKU : MI-{genre}-...
     */
    public function skuCode(): string
    {
        return match ($this) {
            self::Homme => 'H',
            self::Femme => 'F',
        };
    }
}
