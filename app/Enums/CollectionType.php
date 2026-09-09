<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CollectionType: string implements HasLabel
{
    case Manual = 'manual';
    case Rule = 'rule';

    public function getLabel(): string
    {
        return __('enums.collection_type.'.$this->value);
    }
}
