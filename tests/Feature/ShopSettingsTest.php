<?php

use App\Settings\ShopSettings;

it('livre des réglages utilisables dès la migration', function () {
    $settings = app(ShopSettings::class);

    expect($settings->contact_email)->not->toBeEmpty()
        ->and($settings->free_shipping_threshold)->toBe(60000)
        ->and($settings->exchange_days)->toBe(14)
        ->and($settings->announcement_enabled)->toBeFalse();
});

it('rejoue la migration sans écraser les valeurs saisies', function () {
    $settings = app(ShopSettings::class);
    $settings->contact_city = 'Nador';
    $settings->free_shipping_threshold = 80000;
    $settings->save();

    // Un redéploiement peut relancer les migrations : la saisie doit survivre.
    $migration = require database_path('settings/2026_09_14_000000_create_shop_settings.php');
    $migration->up();

    $refreshed = app(ShopSettings::class)->refresh();

    expect($refreshed->contact_city)->toBe('Nador')
        ->and($refreshed->free_shipping_threshold)->toBe(80000);
});
