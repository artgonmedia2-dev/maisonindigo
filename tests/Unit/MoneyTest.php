<?php

use App\Support\Money;

const NBSP = "\u{00A0}";
const THIN_NBSP = "\u{202F}";

it('formate les centimes en dirhams avec virgule décimale', function () {
    expect(Money::format(49900))->toBe('499,00'.NBSP.'dh')
        ->and(Money::format(39950))->toBe('399,50'.NBSP.'dh')
        ->and(Money::format(5))->toBe('0,05'.NBSP.'dh')
        ->and(Money::format(0))->toBe('0,00'.NBSP.'dh');
});

it('sépare les milliers par une espace fine insécable', function () {
    expect(Money::format(129900))->toBe('1'.THIN_NBSP.'299,00'.NBSP.'dh')
        ->and(Money::format(100000000))->toBe('1'.THIN_NBSP.'000'.THIN_NBSP.'000,00'.NBSP.'dh');
});

it('peut omettre le symbole', function () {
    expect(Money::format(49900, withSymbol: false))->toBe('499,00');
});

it('formate les montants négatifs', function () {
    expect(Money::format(-3500))->toBe('-35,00'.NBSP.'dh');
});

it('convertit un décimal en centimes', function () {
    expect(Money::fromDecimal(499))->toBe(49900)
        ->and(Money::fromDecimal('499.00'))->toBe(49900)
        ->and(Money::fromDecimal('499,50'))->toBe(49950)
        ->and(Money::fromDecimal('1 299,00'))->toBe(129900)
        ->and(Money::fromDecimal(0.1 + 0.2))->toBe(30);
});

it('refuse un montant illisible', function () {
    Money::fromDecimal('quatre cents');
})->throws(InvalidArgumentException::class);

it('calcule un pourcentage arrondi au centime', function () {
    expect(Money::percentOf(49900, 10))->toBe(4990)
        ->and(Money::percentOf(39950, 10))->toBe(3995)
        ->and(Money::percentOf(33333, 10))->toBe(3333)
        ->and(Money::toDecimal(49950))->toBe(499.5);
});
