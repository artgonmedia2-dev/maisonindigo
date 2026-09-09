<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum WhatsAppDirection: string implements HasLabel
{
    case Outbound = 'outbound';
    case Inbound = 'inbound';

    public function getLabel(): string
    {
        return __('enums.whatsapp_direction.'.$this->value);
    }
}
