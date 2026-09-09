<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DiscountType: string implements HasLabel
{
    case Percent = 'percent';
    case Fixed = 'fixed';
    case Bundle = 'bundle';

    public function getLabel(): string
    {
        return __('enums.discount_type.'.$this->value);
    }
}
