<?php

use App\Filament\Pages\ManageShopSettings;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\RelationManagers\VariantsRelationManager;
use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingZone;
use App\Models\SizeChart;
use App\Settings\ShopSettings;
use Database\Seeders\DemoCatalogSeeder;
use Database\Seeders\ShippingZonesSeeder;
use Database\Seeders\SizeChartsSeeder;
use Filament\Actions\Testing\TestAction;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    $this->actingAs(Admin::factory()->create(), 'admin');
    $this->seed([SizeChartsSeeder::class, ShippingZonesSeeder::class, DemoCatalogSeeder::class]);
});

it('ouvre chaque écran du back-office', function (string $url) {
    $this->get($url)->assertOk();
})->with([
    'tableau de bord' => '/admin',
    'produits' => '/admin/products',
    'nouveau produit' => '/admin/products/create',
    'collections' => '/admin/collections',
    'nouvelle collection' => '/admin/collections/create',
    'coupes' => '/admin/cuts',
    'nouvelle coupe' => '/admin/cuts/create',
    'lavages' => '/admin/washes',
    'nouveau lavage' => '/admin/washes/create',
    'tableaux de mesures' => '/admin/size-charts',
    'alertes de stock' => '/admin/stock-alerts',
    'commandes' => '/admin/orders',
    'clients' => '/admin/customers',
    'membres' => '/admin/members',
    'nouveau membre' => '/admin/members/create',
    'zones de livraison' => '/admin/shipping-zones',
    'nouvelle zone' => '/admin/shipping-zones/create',
    'remises' => '/admin/discounts',
    'nouvelle remise' => '/admin/discounts/create',
    'réglages' => '/admin/manage-shop-settings',
]);

it('ouvre les fiches de modification', function () {
    $product = Product::query()->firstOrFail();
    $chart = SizeChart::query()->firstOrFail();
    $zone = ShippingZone::query()->firstOrFail();

    $this->get("/admin/products/{$product->id}/edit")->assertOk()->assertSee($product->title);
    $this->get("/admin/size-charts/{$chart->id}/edit")->assertOk();
    $this->get("/admin/shipping-zones/{$zone->id}/edit")->assertOk()->assertSee($zone->name);
});

it('crée un produit et compose son titre', function () {
    Livewire\Livewire::test(CreateProduct::class)
        ->fillForm([
            'gender' => 'homme',
            'cut' => 'straight',
            'wash' => 'noir',
            'status' => 'active',
            'price' => '529.00',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::query()->where('cut', 'straight')->where('wash', 'noir')->firstOrFail();

    expect($product->title)->toBe('Straight Noir')
        ->and($product->slug)->toBe('straight-noir-homme')
        ->and($product->price)->toBe(52900);
});

it('garde le titre écrit à la main', function () {
    Livewire\Livewire::test(CreateProduct::class)
        ->fillForm([
            'gender' => 'homme',
            'cut' => 'straight',
            'wash' => 'gris',
            'title' => 'Le Straight de la maison',
            'status' => 'active',
            'price' => '529.00',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::query()->where('cut', 'straight')->where('wash', 'gris')->firstOrFail();

    // L'adresse suit le titre choisi, pas la composition automatique.
    expect($product->title)->toBe('Le Straight de la maison')
        ->and($product->slug)->toBe('le-straight-de-la-maison-homme');
});

it('reprend la composition quand la coupe change', function () {
    Livewire\Livewire::test(CreateProduct::class)
        ->fillForm(['gender' => 'homme', 'cut' => 'straight', 'wash' => 'gris'])
        ->assertFormSet(['title' => 'Straight Gris'])
        ->fillForm(['cut' => 'slim'])
        ->assertFormSet(['title' => 'Slim Gris'])
        // Un titre personnalisé ne bouge plus.
        ->fillForm(['title' => 'Le Slim de la maison'])
        ->fillForm(['wash' => 'noir'])
        ->assertFormSet(['title' => 'Le Slim de la maison']);
});

it('génère la grille des tailles d’un produit', function () {
    $product = Product::factory()->create();

    Livewire\Livewire::test(VariantsRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => EditProduct::class,
    ])
        ->callAction(
            TestAction::make('generer')->table(),
            data: ['sizes' => [30, 32], 'lengths' => [30, 32, 34], 'stock' => 4],
        )
        ->assertHasNoActionErrors();

    expect($product->variants()->count())->toBe(6)
        ->and($product->variants()->sum('stock'))->toBe(24)
        ->and($product->variants()->where('size', 32)->where('length', 34)->value('sku'))
        ->toBe(ProductVariant::buildSku($product, 32, 34));
});

it('enregistre les réglages et les applique à la boutique', function () {
    Livewire\Livewire::test(ManageShopSettings::class)
        ->fillForm([
            'contact_email' => 'bonjour@maisonindigo.ma',
            'contact_whatsapp' => '+212612345678',
            'contact_city' => 'Nador',
            'free_shipping_threshold' => '800.00',
            'exchange_days' => 21,
            'announcement_enabled' => true,
            'announcement_text' => 'Livraison offerte dès 800 dh.',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = app(ShopSettings::class)->refresh();

    expect($settings->free_shipping_threshold)->toBe(80000)
        ->and($settings->contact_city)->toBe('Nador');

    auth('admin')->logout();

    $this->get(route('home'))->assertInertia(fn (AssertableInertia $page) => $page
        ->where('maison.contact.city', 'Nador')
        ->where('maison.exchange_days', 21)
        ->where('maison.announcement', 'Livraison offerte dès 800 dh.')
        ->where('cart.free_shipping_threshold', 80000)
    );
});
