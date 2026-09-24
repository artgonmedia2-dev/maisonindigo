<?php

use Database\Seeders\ShippingZonesSeeder;

beforeEach(fn () => $this->seed(ShippingZonesSeeder::class));

it('annonce que la réponse dépend de l’en-tête Inertia', function () {
    $response = $this->get('/homme')->assertOk();

    // Sans ce Vary, un cache peut resservir le JSON d'une navigation interne
    // à une navigation normale, et le visiteur voit du JSON brut.
    expect($response->headers->get('Vary'))->toContain('X-Inertia');
});

it('interdit le stockage d’une réponse Inertia JSON', function () {
    // La version se lit sur la page rendue : celle du manifeste d'assets,
    // sinon Inertia répond 409 et renvoie le client vers l'adresse complète.
    $version = $this->get('/homme')->viewData('page')['version'];

    $response = $this->withHeaders([
        'X-Inertia' => 'true',
        'X-Inertia-Version' => $version,
    ])->get('/homme')->assertOk();

    expect($response->headers->get('X-Inertia'))->toBe('true')
        ->and($response->headers->get('Cache-Control'))->toContain('no-store');
});

it('laisse la page HTML se comporter normalement', function () {
    $response = $this->get('/homme')->assertOk();

    expect($response->headers->get('Content-Type'))->toContain('text/html')
        ->and($response->headers->get('X-Inertia'))->toBeNull();
});
