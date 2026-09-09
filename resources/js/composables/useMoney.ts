/** Espace insécable entre le montant et « dh ». */
const NBSP = ' ';

/** Espace fine insécable comme séparateur de milliers. */
const THIN_NBSP = ' ';

export const CURRENCY_SYMBOL = 'dh';

/**
 * Formate un montant en centimes entiers : 49900 → « 499,00 dh ».
 * Miroir exact de App\Support\Money::format côté PHP.
 */
export function formatMoney(cents: number, withSymbol = true): string {
    const sign = cents < 0 ? '-' : '';
    const absolute = Math.abs(Math.round(cents));
    const units = Math.floor(absolute / 100);
    const remainder = absolute % 100;

    const grouped = units
        .toString()
        .replace(/\B(?=(\d{3})+(?!\d))/g, THIN_NBSP);

    const amount = `${sign}${grouped},${remainder.toString().padStart(2, '0')}`;

    return withSymbol ? `${amount}${NBSP}${CURRENCY_SYMBOL}` : amount;
}

/**
 * Convertit un montant décimal en centimes : 499 → 49900.
 */
export function toCents(amount: number): number {
    return Math.round(amount * 100);
}

export function useMoney() {
    return { format: formatMoney, toCents };
}
