<?php

use App\Enums\Gender;
use App\Models\Cut;
use App\Models\Product;
use App\Models\Wash;
use App\Support\CatalogTerms;

it('installe le vocabulaire de la maison', function () {
    expect(Cut::query()->pluck('slug')->all())
        ->toContain('straight', 'wide_leg', 'bootcut')
        ->and(Wash::query()->pluck('slug')->all())
        ->toContain('brut', 'stone', 'ecru');
});

it('répartit les coupes par genre, dans l’ordre choisi', function () {
    $termes = app(CatalogTerms::class);

    // L'ordre suit la colonne position, que le back-office peut réécrire.
    expect($termes->activeCuts(Gender::Homme)->pluck('slug')->all())
        ->toBe(['straight', 'regular', 'slim', 'relaxed', 'tapered'])
        ->and($termes->activeCuts(Gender::Femme)->pluck('slug')->all())
        ->toBe(['straight', 'slim', 'wide_leg', 'mom', 'bootcut', 'flare'])
        ->and($termes->cut('mom')?->isAvailableFor(Gender::Homme))->toBeFalse()
        ->and($termes->cut('straight')?->isAvailableFor(Gender::Femme))->toBeTrue();
});

it('expose des codes SKU courts et uniques', function () {
    $cuts = Cut::query()->pluck('sku_code')->all();
    $washes = Wash::query()->pluck('sku_code')->all();

    expect($cuts)->toHaveCount(count(array_unique($cuts)))
        ->and($washes)->toHaveCount(count(array_unique($washes)))
        ->and(app(CatalogTerms::class)->cut('straight')?->sku_code)->toBe('STR')
        ->and(app(CatalogTerms::class)->wash('brut')?->sku_code)->toBe('BRU');
});

it('écarte une coupe retirée de la vente', function () {
    Cut::query()->where('slug', 'bootcut')->update(['is_active' => false]);

    $proposees = app(CatalogTerms::class)->activeCuts(Gender::Femme)->pluck('slug')->all();

    expect($proposees)->not->toContain('bootcut')
        // Le terme reste connu : les fiches qui l'emploient gardent leur libellé.
        ->and(app(CatalogTerms::class)->cutLabel('bootcut'))->toBe('Bootcut');
});

it('affiche l’identifiant quand le terme a disparu', function () {
    expect(app(CatalogTerms::class)->cutLabel('inconnue'))->toBe('inconnue')
        ->and(app(CatalogTerms::class)->washLabel('inconnu'))->toBe('inconnu');
});

it('compose le titre depuis le vocabulaire', function () {
    expect(Product::composeTitle('straight', 'brut'))->toBe('Straight Indigo Brut')
        ->and(Product::composeTitle('straight', null))->toBe('')
        ->and(Product::composeTitle('inconnue', 'brut'))->toBe('');
});

it('reconnaît un titre composé automatiquement', function () {
    expect(Product::isComposedTitle('Straight Indigo Brut'))->toBeTrue()
        ->and(Product::isComposedTitle(''))->toBeTrue()
        ->and(Product::isComposedTitle(null))->toBeTrue()
        ->and(Product::isComposedTitle('Le Straight de la maison'))->toBeFalse();
});

it('suit le vocabulaire pour le préfixe SKU', function () {
    $product = Product::factory()->create(['gender' => Gender::Homme, 'cut' => 'straight', 'wash' => 'brut']);

    expect($product->skuPrefix())->toBe('MI-H-STR-BRU');
});

it('empêche la suppression d’un terme employé', function () {
    Product::factory()->create(['gender' => Gender::Homme, 'cut' => 'straight', 'wash' => 'brut']);

    expect(Cut::query()->where('slug', 'straight')->first()?->isUsed())->toBeTrue()
        ->and(Cut::query()->where('slug', 'relaxed')->first()?->isUsed())->toBeFalse();
});
