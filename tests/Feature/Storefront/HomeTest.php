<?php

use App\Models\Product;
use Inertia\Testing\AssertableInertia as Assert;

it('charge la page d’accueil', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('meta.description')
            ->has('maison.name')
            ->has('maison.tagline')
            ->has('cart.count')
            ->where('auth.user', null)
        );
});

it('sert la page d’accueil en rendu serveur', function () {
    expect(config('inertia.ssr.enabled'))->toBeTrue();

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('data-page', escape: false)
        ->assertSee('lang="fr"', escape: false)
        ->assertSee('/fonts/cormorant-garamond-latin.woff2', escape: false)
        ->assertSee('/fonts/inter-latin.woff2', escape: false);
});

it('affiche la page 404 dans le ton de la maison', function () {
    $this->get('/cette-page-n-existe-pas')
        ->assertNotFound()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Error')
            ->where('status', 404)
            ->has('maison.name')
            ->has('ziggy.location')
            ->where('flash.success', null)
        );
});

it('résout les routes de navigation', function (string $name) {
    $this->get(route($name))->assertOk();
})->with(['collections.women', 'collections.men', 'collections.new', 'collections.atelier', 'size-quiz']);

it('sert quatre nouveautés, de quoi remplir deux rangées sur téléphone', function () {
    Product::factory()->count(6)->withVariants()->create(['is_new' => true]);

    // La grille est à deux colonnes sur téléphone : trois modèles laisseraient
    // le dernier seul sur sa ligne. Le quatrième est masqué au-delà de 768 px,
    // où la grille passe à trois colonnes.
    $this->get('/')->assertInertia(fn (Assert $page) => $page->has('newProducts', 4));
});

it('se contente de ce que le catalogue contient', function () {
    Product::factory()->count(2)->withVariants()->create(['is_new' => true]);

    $this->get('/')->assertInertia(fn (Assert $page) => $page->has('newProducts', 2));
});
