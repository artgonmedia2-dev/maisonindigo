<?php

use App\Settings\ShopSettings;
use Database\Seeders\ShippingZonesSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(ShippingZonesSeeder::class));

it('sert les sept logos avec leur intitulé', function () {
    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->has('brands.items', 7)
        ->where('brands.items.0.name', 'Levi\'s')
        ->where('brands.items.0.file', 'levis-1.svg')
        ->where('brands.title', 'Les maisons qui ont façonné le denim')
    );
});

it('chaque logo annoncé existe bien dans les fichiers servis', function () {
    $items = $this->get('/')->viewData('page')['props']['brands']['items'];

    foreach ($items as $brand) {
        expect(file_exists(public_path("images/brands/{$brand['file']}")))
            ->toBeTrue("Le logo {$brand['file']} doit être servi.");
    }
});

it('retire le bandeau quand la maison le décide', function () {
    app(ShopSettings::class)->fill(['home_brands_enabled' => false])->save();

    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('brands.items', []));
});

it('laisse changer l’intitulé depuis le back-office', function () {
    app(ShopSettings::class)->fill(['home_brands_title' => 'Les marques disponibles en boutique'])->save();

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('brands.title', 'Les marques disponibles en boutique')
    );
});
