<?php

use Illuminate\Support\Facades\File;

afterEach(function () {
    File::delete(File::glob(storage_path('logs/laravel-pest-*.log')));
});

it('annonce l’absence d’erreur quand le journal est vide', function () {
    $this->artisan('mi:logs')->assertSuccessful();
});

it('affiche les dernières erreurs, les informations mises de côté', function () {
    File::put(storage_path('logs/laravel-pest-'.now()->format('Y-m-d').'.log'), <<<'LOG'
    [2026-09-21 10:00:00] production.INFO: WhatsApp non configuré
    [2026-09-21 10:01:00] production.ERROR: Premiere panne
    #0 une trace
    [2026-09-21 10:02:00] production.ERROR: Seconde panne
    #0 une autre trace
    LOG);

    $this->artisan('mi:logs --lines=1')
        ->expectsOutputToContain('Seconde panne')
        ->doesntExpectOutputToContain('WhatsApp non configuré')
        ->assertSuccessful();
});
