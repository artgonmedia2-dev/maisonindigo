<?php

use App\Enums\Gender;
use App\Models\Product;
use App\Settings\ShopSettings;
use Database\Seeders\ShippingZonesSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(ShippingZonesSeeder::class));

it('reprend les textes réglés dans le back-office', function () {
    app(ShopSettings::class)->fill([
        'home_hero_kicker' => 'Maison de Nador',
        'home_hero_title' => 'Le bleu, bien coupé.',
        'home_hero_lead' => 'Une toile dense, dix-sept tailles.',
        'home_hero_cta_label' => 'Voir les baggys',
        'home_hero_cta_url' => '/homme/jean-baggy',
    ])->save();

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('hero.kicker', 'Maison de Nador')
        ->where('hero.title', 'Le bleu, bien coupé.')
        ->where('hero.cta.label', 'Voir les baggys')
        ->where('hero.cta.url', '/homme/jean-baggy')
    );
});

it('annonce le prix d’appel du catalogue', function () {
    Product::factory()->withVariants()->create(['price' => 59900]);
    Product::factory()->withVariants()->create(['price' => 44900]);
    // Un brouillon ne doit pas tirer le prix d'appel vers le bas.
    Product::factory()->draft()->withVariants()->create(['price' => 19900]);

    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('hero.from_price', 44900));
});

it('met en avant un modèle, avec son adresse et son prix', function () {
    Product::factory()->withVariants()->create(['gender' => Gender::Homme, 'cut' => 'slim', 'wash' => 'noir', 'price' => 59900]);
    $vedette = Product::factory()->featured()->withVariants()->create(['gender' => Gender::Homme, 'cut' => 'baggy', 'wash' => 'brut', 'price' => 54900]);

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('hero.product.id', $vedette->id)
        ->where('hero.product.url', $vedette->path())
        ->where('hero.product.price', 54900)
    );
});

it('se passe de prix et de modèle quand le catalogue est vide', function () {
    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('hero.from_price', null)
        ->where('hero.product', null)
    );
});
