<?php

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
