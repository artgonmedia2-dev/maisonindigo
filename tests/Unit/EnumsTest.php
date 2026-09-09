<?php

use App\Enums\Cut;
use App\Enums\Gender;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\Wash;

it('répartit les coupes par genre', function () {
    expect(Cut::forGender(Gender::Homme))->toBe([Cut::Straight, Cut::Regular, Cut::Slim, Cut::Relaxed, Cut::Tapered])
        ->and(Cut::forGender(Gender::Femme))->toBe([Cut::WideLeg, Cut::Straight, Cut::Mom, Cut::Slim, Cut::Bootcut, Cut::Flare])
        ->and(Cut::Mom->isAvailableFor(Gender::Homme))->toBeFalse()
        ->and(Cut::Straight->isAvailableFor(Gender::Femme))->toBeTrue()
        ->and(Gender::Femme->cuts())->toHaveCount(6);
});

it('expose des codes SKU courts et uniques', function () {
    $cutCodes = array_map(fn (Cut $cut) => $cut->skuCode(), Cut::cases());
    $washCodes = array_map(fn (Wash $wash) => $wash->skuCode(), Wash::cases());

    expect($cutCodes)->toHaveCount(count(array_unique($cutCodes)))
        ->and($washCodes)->toHaveCount(count(array_unique($washCodes)))
        ->and(Gender::Homme->skuCode())->toBe('H')
        ->and(Gender::Femme->skuCode())->toBe('F')
        ->and(Cut::Straight->skuCode())->toBe('STR')
        ->and(Wash::Brut->skuCode())->toBe('BRU');
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
    $enums = [...Gender::cases(), ...Cut::cases(), ...Wash::cases(), ...OrderStatus::cases(), ...PaymentMethod::cases()];

    foreach ($enums as $case) {
        $label = $case->getLabel();

        expect($label)->not->toBe('')
            ->and($label)->not->toContain('!')
            ->and($label)->not->toStartWith('enums.');
    }
});
