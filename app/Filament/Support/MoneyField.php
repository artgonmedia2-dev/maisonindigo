<?php

namespace App\Filament\Support;

use App\Support\Money;
use Filament\Forms\Components\TextInput;

/**
 * Champ monétaire du back-office : saisie en dirhams, stockage en centimes entiers.
 */
final class MoneyField
{
    public static function make(string $name, string $label): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->numeric()
            ->minValue(0)
            ->step(0.01)
            ->suffix(__('admin.common.dh'))
            ->formatStateUsing(fn (int|string|null $state): ?string => $state === null || $state === ''
                ? null
                : number_format((int) $state / 100, 2, '.', ''))
            ->dehydrateStateUsing(fn (int|string|null $state): ?int => $state === null || $state === ''
                ? null
                : Money::fromDecimal($state));
    }
}
