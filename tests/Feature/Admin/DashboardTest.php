<?php

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\Member;
use App\Models\Order;
use App\Models\Product;

it('accueille l’administrateur par son prénom sur le tableau de bord', function () {
    $admin = Admin::factory()->create(['name' => 'Youssef']);

    $this->actingAs($admin, 'admin')
        ->get('/admin')
        ->assertOk()
        ->assertSee('Bonjour Youssef.')
        ->assertSee(__('admin.dashboard.title'));
});

it('affiche les chiffres de la maison', function () {
    $admin = Admin::factory()->create();

    Product::factory()->count(2)->create(['status' => ProductStatus::Active]);
    Product::factory()->create(['status' => ProductStatus::Draft]);
    Order::factory()->count(3)->create(['status' => OrderStatus::New]);
    Order::factory()->create(['status' => OrderStatus::Delivered]);
    Customer::factory()->count(4)->create();
    Member::factory()->count(5)->create();

    $response = $this->actingAs($admin, 'admin')->get('/admin')->assertOk();

    foreach (['active_products', 'new_orders', 'customers', 'members'] as $key) {
        $response->assertSee(__("admin.stats.$key"));
    }

    $html = $response->getContent();

    expect($html)
        ->toContain('Produits actifs')
        ->and(substr_count($html, 'fi-wi-stats-overview-stat-value'))->toBe(6);
});

it('charge le thème de la maison et désactive le mode sombre', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')
        ->get('/admin')
        ->assertOk()
        ->assertSee('mi-brand', escape: false)
        ->assertDontSee('fi-theme-switcher', escape: false);
});
