<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Montants en centimes entiers (MAD). Seul point de formatage côté PHP.
 *
 * Money::format(49900) => « 499,00 dh »
 */
final class Money
{
    public const CURRENCY = 'MAD';

    public const SYMBOL = 'dh';

    /** Espace insécable entre le montant et « dh ». */
    private const NBSP = "\u{00A0}";

    /** Espace fine insécable comme séparateur de milliers. */
    private const THIN_NBSP = "\u{202F}";

    public static function format(int $cents, bool $withSymbol = true): string
    {
        $amount = number_format($cents / 100, 2, ',', self::THIN_NBSP);

        return $withSymbol ? $amount.self::NBSP.self::SYMBOL : $amount;
    }

    /**
     * Convertit un montant décimal (« 499 », « 499.00 », « 499,00 ») en centimes.
     */
    public static function fromDecimal(string|int|float $amount): int
    {
        if (is_string($amount)) {
            $normalized = str_replace([' ', self::NBSP, self::THIN_NBSP], '', $amount);
            $normalized = str_replace(',', '.', $normalized);

            if (! is_numeric($normalized)) {
                throw new InvalidArgumentException("Montant invalide : {$amount}");
            }

            $amount = (float) $normalized;
        }

        return (int) round($amount * 100);
    }

    public static function toDecimal(int $cents): float
    {
        return round($cents / 100, 2);
    }

    /**
     * Pourcentage d'un montant, arrondi au centime.
     */
    public static function percentOf(int $cents, int|float $percent): int
    {
        return (int) round($cents * $percent / 100);
    }
}
