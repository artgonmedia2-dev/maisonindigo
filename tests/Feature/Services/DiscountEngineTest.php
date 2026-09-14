<?php

use App\Enums\DiscountType;
use App\Models\Discount;
use App\Services\DiscountEngine;

it('ne remise rien sans règle applicable', function () {
    $result = app(DiscountEngine::class)->compute(49900, 1);

    expect($result->total)->toBe(0)->and($result->isApplied())->toBeFalse()->and($result->codeError)->toBeNull();
});

it('applique la remise automatique dès deux jeans', function () {
    Discount::factory()->bundle()->create();

    $engine = app(DiscountEngine::class);

    expect($engine->compute(49900, 1)->total)->toBe(0)
        ->and($engine->compute(99800, 2)->total)->toBe(9980)
        ->and($engine->compute(99800, 2)->label)->toBe('Deux jeans, dix pour cent')
        ->and($engine->compute(99800, 2)->code)->toBeNull();
});

it('applique un code en pourcentage ou en montant fixe', function () {
    Discount::factory()->create(['code' => 'INDIGO10', 'type' => DiscountType::Percent, 'value' => 10]);
    Discount::factory()->create(['code' => 'CINQUANTE', 'type' => DiscountType::Fixed, 'value' => 5000]);

    $engine = app(DiscountEngine::class);

    expect($engine->compute(49900, 1, 'indigo10')->total)->toBe(4990)
        ->and($engine->compute(49900, 1, 'INDIGO10')->code)->toBe('INDIGO10')
        ->and($engine->compute(49900, 1, 'CINQUANTE')->total)->toBe(5000)
        ->and($engine->compute(3000, 1, 'CINQUANTE')->total)->toBe(3000);
});

it('signale un code invalide, expiré ou épuisé sans bloquer', function () {
    Discount::factory()->create(['code' => 'FINI', 'ends_at' => now()->subDay()]);
    Discount::factory()->create(['code' => 'EPUISE', 'usage_limit' => 1, 'usage_count' => 1]);

    $engine = app(DiscountEngine::class);

    foreach (['INCONNU', 'FINI', 'EPUISE'] as $code) {
        $result = $engine->compute(49900, 1, $code);
        expect($result->total)->toBe(0)->and($result->codeError)->not->toBeNull();
    }
});

it('garde la remise la plus avantageuse sans cumuler', function () {
    Discount::factory()->bundle()->create();
    Discount::factory()->create(['code' => 'PETIT', 'value' => 5]);
    Discount::factory()->create(['code' => 'GRAND', 'value' => 20]);

    $engine = app(DiscountEngine::class);

    expect($engine->compute(99800, 2, 'PETIT')->total)->toBe(9980)
        ->and($engine->compute(99800, 2, 'PETIT')->code)->toBeNull()
        ->and($engine->compute(99800, 2, 'GRAND')->total)->toBe(19960)
        ->and($engine->compute(99800, 2, 'GRAND')->code)->toBe('GRAND');
});
