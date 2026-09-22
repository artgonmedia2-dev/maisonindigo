<?php

use App\Enums\Gender;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;

it('expose des codes SKU de genre courts', function () {
    expect(Gender::Homme->skuCode())->toBe('H')
        ->and(Gender::Femme->skuCode())->toBe('F');
});

it('décrit le graphe des statuts de commande', function () {
    expect(OrderStatus::New->canTransitionTo(OrderStatus::Confirmed))->toBeTrue()
        ->and(OrderStatus::New->canTransitionTo(OrderStatus::ToCallback))->toBeTrue()
        ->and(OrderStatus::New->canTransitionTo(OrderStatus::Shipped))->toBeFalse()
        ->and(OrderStatus::Confirmed->canTransitionTo(OrderStatus::Prepared))->toBeTrue()
        ->and(OrderStatus::Prepared->canTransitionTo(OrderStatus::Shipped))->toBeTrue()
        ->and(OrderStatus::Shipped->canTransitionTo(OrderStatus::Delivered))->toBeTrue()
        ->and(OrderStatus::Delivered->canTransitionTo(OrderStatus::Returned))->toBeTrue()
        ->and(OrderStatus::Cancelled->isFinal())->toBeTrue()
        ->and(OrderStatus::Returned->isFinal())->toBeTrue()
        ->and(OrderStatus::Cancelled->releasesStock())->toBeTrue()
        ->and(OrderStatus::Returned->releasesStock())->toBeFalse();
});

it('a des libellés français en vouvoiement, sans point d’exclamation', function () {
    $enums = [...Gender::cases(), ...OrderStatus::cases(), ...PaymentMethod::cases()];

    foreach ($enums as $case) {
        $label = $case->getLabel();

        expect($label)->not->toBe('')
            ->and($label)->not->toContain('!')
            ->and($label)->not->toStartWith('enums.');
    }
});
