<?php

use App\Models\Admin;
use Database\Seeders\ShippingZonesSeeder;
use Illuminate\Support\Facades\File;

it('signale les points bloquants d’un environnement mal réglé', function () {
    config()->set('app.debug', true);
    config()->set('inertia.ssr.enabled', true);

    $this->artisan('mi:deploy-check')
        ->expectsOutputToContain('Débogage désactivé')
        ->expectsOutputToContain('Rendu serveur cohérent')
        ->assertFailed();
});

it('accepte un environnement de production correctement réglé', function () {
    config()->set('app.env', 'production');
    config()->set('app.debug', false);
    config()->set('app.url', 'https://maisonindigo.ma');
    config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    config()->set('inertia.ssr.enabled', false);
    config()->set('maison.trusted_proxies', '*');
    config()->set('maison.force_https', true);
    Admin::factory()->create();
    $this->seed(ShippingZonesSeeder::class);
    app()->detectEnvironment(fn (): string => 'production');

    // Les assets compilés existent sur un vrai serveur comme en local.
    expect(File::exists(public_path('build/manifest.json')))->toBeTrue();

    $this->artisan('mi:deploy-check')
        ->expectsOutputToContain('Version en ligne de commande')
        ->expectsOutputToContain('Un administrateur existe')
        ->assertSuccessful();
});

it('rend les avertissements bloquants avec --strict', function () {
    config()->set('app.env', 'production');
    config()->set('app.debug', false);
    config()->set('app.url', 'https://maisonindigo.ma');
    config()->set('inertia.ssr.enabled', false);
    config()->set('maison.trusted_proxies', null);
    Admin::factory()->create();
    $this->seed(ShippingZonesSeeder::class);
    app()->detectEnvironment(fn (): string => 'production');

    $this->artisan('mi:deploy-check --strict')->assertFailed();
});

it('ne laisse aucun fichier de contrôle derrière lui', function () {
    $before = File::exists(storage_path('app/public'))
        ? count(File::files(storage_path('app/public')))
        : 0;

    $this->artisan('mi:deploy-check');

    $after = File::exists(storage_path('app/public'))
        ? count(File::files(storage_path('app/public')))
        : 0;

    // Le contrôle du lien écrit un fichier témoin : il doit être effacé.
    expect($after)->toBe($before)
        ->and(File::glob(storage_path('app/public/controle-*.txt')))->toBe([]);
});

it('signale des vignettes laissées en file d’attente', function () {
    config()->set('media-library.queue_conversions_by_default', true);
    config()->set('queue.default', 'database');

    $this->artisan('mi:deploy-check')
        ->expectsOutputToContain('Conversions d’images immédiates')
        ->expectsOutputToContain('QUEUE_CONVERSIONS_BY_DEFAULT');

    config()->set('media-library.queue_conversions_by_default', false);

    $this->artisan('mi:deploy-check')
        ->doesntExpectOutputToContain('QUEUE_CONVERSIONS_BY_DEFAULT');
});
