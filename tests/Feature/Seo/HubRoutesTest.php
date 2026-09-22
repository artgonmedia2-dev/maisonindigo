<?php

use App\Enums\Gender;
use App\Models\Product;
use Database\Seeders\ShippingZonesSeeder;

beforeEach(fn () => $this->seed(ShippingZonesSeeder::class));

it('sert le hub de coupe', function () {
    Product::factory()->withVariants()->create(['gender' => Gender::Homme, 'cut' => 'baggy', 'wash' => 'brut']);

    $this->get('/homme/jean-baggy')
        ->assertOk()
        ->assertSee('Jean baggy homme', escape: false);
});

it('refuse une coupe absente du genre', function () {
    $this->get('/homme/jean-mom')->assertNotFound();
});

it('refuse une coupe inconnue', function () {
    $this->get('/homme/jean-inexistante')->assertNotFound();
});

it('sert les quatre lavages indexables', function (string $wash) {
    Product::factory()->withVariants()->create(['gender' => Gender::Homme, 'cut' => 'baggy', 'wash' => 'brut']);

    $this->get("/homme/jean-baggy/{$wash}")->assertOk();
})->with(['noir', 'bleu', 'brut', 'clair']);

it('refuse un lavage hors de la liste indexable', function (string $wash) {
    $this->get("/homme/jean-baggy/{$wash}")->assertNotFound();
})->with(['stone', 'gris', 'ecru', 'nimporte-quoi']);

it('sert la fiche produit sous son genre', function () {
    $product = Product::factory()->withVariants()->create(['gender' => Gender::Homme, 'cut' => 'baggy', 'wash' => 'noir']);

    $this->get("/homme/{$product->slug}")->assertOk();
});

it('renvoie l’ancienne adresse produit vers la nouvelle', function () {
    $product = Product::factory()->create(['gender' => Gender::Homme, 'cut' => 'baggy', 'wash' => 'stone']);

    $this->get("/produit/{$product->slug}")
        ->assertRedirect("/homme/{$product->slug}")
        ->assertStatus(301);
});

it('sert le Journal', function () {
    $this->get('/journal')->assertOk();
});
