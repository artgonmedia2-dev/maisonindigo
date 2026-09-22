<?php

use App\Enums\Gender;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\DiscountEngine;
use Database\Seeders\ShippingZonesSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(ShippingZonesSeeder::class));

it('n’annonce aucune offre sans remise en lot', function () {
    $product = Product::factory()->withVariants()->create(['price' => 54900]);

    $this->get($product->path())->assertInertia(fn (Assert $page) => $page->where('pack', null));
});

it('annonce le lot au prix que le panier facturera', function () {
    Discount::factory()->bundle()->create();
    $product = Product::factory()->withVariants()->create(['price' => 54900]);

    $offre = app(DiscountEngine::class)->packOffer(54900);

    // Le prix affiché est celui du moteur : une seule source de vérité.
    $applique = app(DiscountEngine::class)->compute(109800, 2);

    expect($offre)->not->toBeNull()
        ->and($offre['quantity'])->toBe(2)
        ->and($offre['subtotal'])->toBe(109800)
        ->and($offre['total'])->toBe(109800 - $applique->total)
        ->and($offre['unit'])->toBe(intdiv($offre['total'], 2))
        ->and($offre['total'])->toBeLessThan($offre['subtotal']);

    $this->get($product->path())->assertInertia(fn (Assert $page) => $page
        ->where('pack.quantity', 2)
        ->where('pack.total', $offre['total'])
    );
});

it('expose les autres lavages de la même coupe', function () {
    $brut = Product::factory()->withVariants()->create(['gender' => Gender::Homme, 'cut' => 'baggy', 'wash' => 'brut']);
    Product::factory()->withVariants()->create(['gender' => Gender::Homme, 'cut' => 'baggy', 'wash' => 'noir']);
    // Autre coupe : hors du sélecteur de coloris.
    Product::factory()->withVariants()->create(['gender' => Gender::Homme, 'cut' => 'slim', 'wash' => 'noir']);
    // Autre genre : hors du sélecteur également.
    Product::factory()->withVariants()->create(['gender' => Gender::Femme, 'cut' => 'baggy', 'wash' => 'noir']);

    $this->get($brut->path())->assertInertia(fn (Assert $page) => $page
        ->has('siblings', 2)
        ->where('siblings.0.current', true)
        ->where('siblings.1.current', false)
    );
});

it('tait le sélecteur quand la coupe n’a qu’un lavage', function () {
    $seul = Product::factory()->withVariants()->create(['gender' => Gender::Homme, 'cut' => 'baggy', 'wash' => 'brut']);

    $this->get($seul->path())->assertInertia(fn (Assert $page) => $page->where('siblings', []));
});

it('ajoute les deux lignes d’un lot en une requête', function () {
    $product = Product::factory()->withVariants()->create();
    $variants = ProductVariant::query()->where('product_id', $product->id)->take(2)->get();

    $this->post(route('cart.store'), [
        'items' => [
            ['variant_id' => $variants[0]->id, 'qty' => 1],
            ['variant_id' => $variants[1]->id, 'qty' => 1],
        ],
    ])->assertRedirect();

    $this->get('/panier')->assertInertia(fn (Assert $page) => $page->where('cart.count', 2));
});

it('n’ajoute rien quand la seconde taille manque', function () {
    $product = Product::factory()->create();
    $disponible = ProductVariant::factory()->for($product)->create(['size' => 32, 'length' => 32, 'stock' => 5]);
    $rupture = ProductVariant::factory()->for($product)->create(['size' => 34, 'length' => 32, 'stock' => 0]);

    $this->post(route('cart.store'), [
        'items' => [
            ['variant_id' => $disponible->id, 'qty' => 1],
            ['variant_id' => $rupture->id, 'qty' => 1],
        ],
    ])->assertSessionHasErrors('variant_id');

    // Le panier reste vide : pas de demi-lot.
    $this->get('/panier')->assertInertia(fn (Assert $page) => $page->where('cart.count', 0));
});
