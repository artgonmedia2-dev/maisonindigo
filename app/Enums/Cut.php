<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Cut: string implements HasLabel
{
    // Homme
    case Straight = 'straight';
    case Regular = 'regular';
    case Slim = 'slim';
    case Relaxed = 'relaxed';
    case Tapered = 'tapered';

    // Femme (Straight et Slim sont communs aux deux genres)
    case WideLeg = 'wide_leg';
    case Mom = 'mom';
    case Bootcut = 'bootcut';
    case Flare = 'flare';

    public function getLabel(): string
    {
        return __('enums.cut.'.$this->value);
    }

    /**
     * Code utilisé dans le SKU : MI-{genre}-{coupe}-...
     */
    public function skuCode(): string
    {
        return match ($this) {
            self::Straight => 'STR',
            self::Regular => 'REG',
            self::Slim => 'SLM',
            self::Relaxed => 'RLX',
            self::Tapered => 'TAP',
            self::WideLeg => 'WID',
            self::Mom => 'MOM',
            self::Bootcut => 'BOO',
            self::Flare => 'FLA',
        };
    }

    /**
     * @return list<Cut>
     */
    public static function forGender(Gender $gender): array
    {
        return match ($gender) {
            Gender::Homme => [self::Straight, self::Regular, self::Slim, self::Relaxed, self::Tapered],
            Gender::Femme => [self::WideLeg, self::Straight, self::Mom, self::Slim, self::Bootcut, self::Flare],
        };
    }

    public function isAvailableFor(Gender $gender): bool
    {
        return in_array($this, self::forGender($gender), true);
    }
}
