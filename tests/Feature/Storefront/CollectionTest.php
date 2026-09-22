<?php

use App\Enums\Gender;
use App\Models\Product;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Product::factory()->withVariants()->create(['gender' => Gender::Femme, 'cut' => 'wide_leg', 'wash' => 'stone', 'price' => 54900, 'is_new' => true]);
    Product::factory()->withVariants()->create(['gender' => Gender::Femme, 'cut' => 'slim', 'wash' => 'noir', 'price' => 44900]);
    Product::factory()->withVariants()->create(['gender' => Gender::Homme, 'cut' => 'straight', 'wash' => 'brut', 'price' => 49900, 'is_atelier' => true]);
    Product::factory()->draft()->withVariants()->create(['gender' => Gender::Homme, 'cut' => 'slim', 'wash' => 'gris']);
});

it('affiche la collection femme avec ses seuls modèles actifs', function () {
    $this->get(route('collections.women'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Collection/Show')
            ->where('handle', 'women')
            ->has('products', 2)
            ->where('products.0.gender', 'femme')
            ->where('products.0.title', 'Wide Leg Stone')
            ->where('products.0.patch', 'new')
            ->where('products.0.price', 54900)
            ->where('products.0.in_stock', true)
            ->has('options.cuts', 7)
            ->has('options.sizes')
            ->where('pagination.total', 2)
        );
});

it('cache les brouillons de la collection homme', function () {
    $this->get(route('collections.men'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('products', 1)
            ->where('products.0.title', 'Straight Indigo Brut')
            ->where('products.0.patch', 'atelier')
        );
});

it('filtre par coupe, lavage et taille', function () {
    $this->get(route('collections.women', ['cut' => ['slim']]))
        ->assertInertia(fn (Assert $page) => $page->has('products', 1)->where('products.0.cut', 'slim')->where('filters.cut.0', 'slim'));

    $this->get(route('collections.women', ['wash' => ['stone']]))
        ->assertInertia(fn (Assert $page) => $page->has('products', 1)->where('products.0.wash', 'stone'));

    $this->get(route('collections.women', ['size' => 28]))
        ->assertInertia(fn (Assert $page) => $page->has('products', 2)->where('filters.size', 28));

    $this->get(route('collections.women', ['size' => 27]))
        ->assertInertia(fn (Assert $page) => $page->has('products', 0));
});

it('trie par prix', function () {
    $this->get(route('collections.women', ['sort' => 'price_asc']))
        ->assertInertia(fn (Assert $page) => $page->where('products.0.price', 44900)->where('filters.sort', 'price_asc'));

    $this->get(route('collections.women', ['sort' => 'price_desc']))
        ->assertInertia(fn (Assert $page) => $page->where('products.0.price', 54900));
});

it('ignore les filtres inconnus', function () {
    $this->get(route('collections.women', ['cut' => ['inconnue'], 'sort' => 'hasard', 'size' => 99]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('products', 2)->where('filters.sort', 'new')->where('filters.size', null));
});

it('réserve nouveautés et atelier aux produits marqués', function () {
    $this->get(route('collections.new'))
        ->assertInertia(fn (Assert $page) => $page->has('products', 1)->where('products.0.title', 'Wide Leg Stone'));

    $this->get(route('collections.atelier'))
        ->assertInertia(fn (Assert $page) => $page->has('products', 1)->where('products.0.title', 'Straight Indigo Brut'));
});

it('met la liste en cache et l’invalide à la sauvegarde d’un produit', function () {
    $this->get(route('collections.men'))->assertInertia(fn (Assert $page) => $page->has('products', 1));

    Product::factory()->withVariants()->create(['gender' => Gender::Homme, 'cut' => 'regular', 'wash' => 'stone']);

    $this->get(route('collections.men'))->assertInertia(fn (Assert $page) => $page->has('products', 2));
});
