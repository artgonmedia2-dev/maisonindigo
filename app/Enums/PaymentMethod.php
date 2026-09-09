<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel
{
    case Cod = 'cod';
    case Transfer = 'transfer';

    public function getLabel(): string
    {
        return __('enums.payment_method.'.$this->value);
    }
}
