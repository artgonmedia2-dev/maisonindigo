<?php

use App\Enums\Gender;
use App\Models\Product;
use App\Settings\ShopSettings;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

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

it('place les deux collections juste après le hero', function () {
    app(ShopSettings::class)->fill([
        'home_collections_first' => true,
        'home_collections_kicker' => 'Deux maisons',
        'home_collections_title' => 'Choisissez votre côté.',
        'home_women_title' => 'Pour elle',
        'home_women_text' => 'Six coupes.',
        'home_men_title' => 'Pour lui',
        'home_men_text' => 'Cinq coupes.',
    ])->save();

    Product::factory()->withVariants()->create(['gender' => Gender::Femme, 'cut' => 'wide_leg', 'wash' => 'stone']);
    Product::factory()->withVariants()->create(['gender' => Gender::Homme, 'cut' => 'straight', 'wash' => 'brut']);

    $this->get('/')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('collections.first', true)
        ->where('collections.kicker', 'Deux maisons')
        ->where('collections.title', 'Choisissez votre côté.')
        ->where('collections.women.title', 'Pour elle')
        ->where('collections.women.count', 1)
        ->where('collections.men.title', 'Pour lui')
        ->where('collections.men.count', 1)
    );
});

it('renvoie la section après les nouveautés quand la maison le décide', function () {
    app(ShopSettings::class)->fill(['home_collections_first' => false])->save();

    $this->get('/')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('collections.first', false)
        ->where('collections.women.count', 0)
    );
});

it('affiche le visuel et le badge choisis pour chaque collection', function () {
    Storage::fake('public');
    Storage::disk('public')->put('accueil/femme.jpg', 'image');

    app(ShopSettings::class)->fill([
        'home_women_image' => 'accueil/femme.jpg',
        'home_women_badge' => 'limited',
        // Fichier absent du disque : la carte doit revenir au gabarit denim.
        'home_men_image' => 'accueil/disparu.jpg',
        'home_men_badge' => null,
    ])->save();

    $this->get('/')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('collections.women.image', Storage::disk('public')->url('accueil/femme.jpg'))
        ->where('collections.women.badge', 'limited')
        ->where('collections.men.image', null)
        ->where('collections.men.badge', null)
    );
});
