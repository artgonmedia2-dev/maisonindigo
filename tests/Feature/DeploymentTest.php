<?php

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\URL;

it('répond sur le point de santé', function () {
    $this->get('/up')->assertOk();
});

it('vide la file d’attente chaque minute quand elle est en base de données', function () {
    $this->artisan('schedule:list')
        ->expectsOutputToContain('queue:work database')
        ->assertSuccessful();

    $events = collect(app(Schedule::class)->events())
        ->filter(fn (Event $event): bool => str_contains($event->command ?? '', 'queue:work database'));

    expect($events)->toHaveCount(1)
        ->and($events->first()?->expression)->toBe('* * * * *');
});

it('fait confiance aux proxys déclarés dans la configuration', function () {
    config()->set('maison.trusted_proxies', '*');

    $response = $this->get('/up', ['X-Forwarded-Proto' => 'https', 'X-Forwarded-For' => '41.140.0.1']);

    $response->assertOk();
    expect(request()->isSecure())->toBeTrue()
        ->and(request()->ip())->toBe('41.140.0.1');
});

it('ignore les en-têtes de proxy quand aucun proxy n’est déclaré', function () {
    config()->set('maison.trusted_proxies', null);

    $this->get('/up', ['X-Forwarded-Proto' => 'https', 'X-Forwarded-For' => '41.140.0.1']);

    expect(request()->isSecure())->toBeFalse()
        ->and(request()->ip())->not->toBe('41.140.0.1');
});

it('génère des URL en https quand FORCE_HTTPS est actif', function () {
    URL::forceScheme('https');

    expect(route('home'))->toStartWith('https://');
});

it('affiche la page de maintenance dans le ton de la maison', function () {
    $html = view('errors.503')->render();

    expect($html)->toContain('La maison est en atelier.')
        ->toContain('Maison Indigo · Maroc')
        ->toContain('cormorant-garamond-latin.woff2');
});

it('limite les formulaires publics de compte', function () {
    foreach (range(1, 6) as $attempt) {
        $response = $this->post('/forgot-password', ['email' => 'inconnu@exemple.ma']);
    }

    // La sixième tentative en une minute est refusée.
    expect($response->getStatusCode())->toBe(429);
});
