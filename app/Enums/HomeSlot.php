<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Les deux emplacements de la section « Deux collections » sur l'accueil.
 *
 * Une collection y est mise en avant avec son visuel et son badge : c'est la
 * fiche de la collection qui commande la carte, et rien d'autre.
 */
enum HomeSlot: string implements HasLabel
{
    case Women = 'women';

    case Men = 'men';

    public function getLabel(): string
    {
        return __('enums.home_slot.'.$this->value);
    }
}
