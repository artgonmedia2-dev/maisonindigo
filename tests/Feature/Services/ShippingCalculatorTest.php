<?php

use App\Models\ShippingZone;
use App\Services\ShippingCalculator;
use Database\Seeders\ShippingZonesSeeder;

beforeEach(function () {
    $this->seed(ShippingZonesSeeder::class);
});

it('trouve la zone d’une ville, sans tenir compte de la casse', function () {
    $calculator = app(ShippingCalculator::class);

    expect($calculator->zoneForCity('casablanca')->name)->toBe('Casablanca')
        ->and($calculator->zoneForCity(' Tanger ')->name)->toBe('Tanger · Tétouan')
        ->and($calculator->zoneForCity('Zagora')->name)->toBe('Autres villes');
});

it('calcule le coût et le seuil de livraison offerte', function () {
    $calculator = app(ShippingCalculator::class);

    $quote = $calculator->quote('Rabat', 49900);
    expect($quote->cost)->toBe(3500)->and($quote->isFree())->toBeFalse()->and($quote->delay)->toBe('24 à 48 h');

    $free = $calculator->quote('Rabat', 60000);
    expect($free->cost)->toBe(0)->and($free->isFree())->toBeTrue();

    $far = $calculator->quote('Zagora', 10000);
    expect($far->cost)->toBe(4900)->and($far->zoneName)->toBe('Autres villes');

    expect($calculator->freeThreshold())->toBe(60000);
});

it('liste les villes connues, triées', function () {
    $cities = app(ShippingCalculator::class)->cities();

    expect($cities)->toContain('Casablanca', 'Rabat', 'Tanger')
        ->and($cities)->toBe(collect($cities)->sort(fn (string $a, string $b): int => strcoll($a, $b))->values()->all());
});

it('invalide le cache quand une zone change', function () {
    $calculator = app(ShippingCalculator::class);
    $calculator->zones();

    ShippingZone::query()->where('name', 'Casablanca')->firstOrFail()->update(['delay' => '24 h']);

    expect(app(ShippingCalculator::class)->quote('Casablanca', 0)->delay)->toBe('24 h');
});
