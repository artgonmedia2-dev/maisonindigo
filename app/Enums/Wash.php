<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Wash: string implements HasLabel
{
    case Brut = 'brut';
    case Stone = 'stone';
    case Clair = 'clair';
    case Noir = 'noir';
    case Gris = 'gris';
    case Ecru = 'ecru';

    public function getLabel(): string
    {
        return __('enums.wash.'.$this->value);
    }

    /**
     * Code utilisé dans le SKU : MI-{genre}-{coupe}-{lavage}-...
     */
    public function skuCode(): string
    {
        return match ($this) {
            self::Brut => 'BRU',
            self::Stone => 'STO',
            self::Clair => 'CLA',
            self::Noir => 'NOI',
            self::Gris => 'GRI',
            self::Ecru => 'ECR',
        };
    }
}
