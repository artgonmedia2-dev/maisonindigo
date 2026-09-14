<?php

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderItem;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(Admin::factory()->create(), 'admin');
});

it('liste les commandes avec leur statut et leur total', function () {
    $order = Order::factory()->create(['number' => 'MI-2026-000007', 'total' => 53400]);
    OrderItem::factory()->for($order)->create();

    $this->get('/admin/orders')
        ->assertOk()
        ->assertSee('MI-2026-000007')
        ->assertSee('534,00')
        ->assertSee('Nouvelle')
        ->assertSee('Paiement à la livraison');
});

it('affiche le détail d’une commande', function () {
    $order = Order::factory()->create(['number' => 'MI-2026-000008']);
    $item = OrderItem::factory()->for($order)->create(['size' => 32, 'length' => 32]);

    $this->get("/admin/orders/{$order->id}")
        ->assertOk()
        ->assertSee('MI-2026-000008')
        ->assertSee($item->title)
        ->assertSee('32 / 32')
        ->assertSee($order->shipping_address['name']);
});

it('change le statut depuis la fiche, via l’action métier', function () {
    $order = Order::factory()->create();

    Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
        ->callAction('transition', data: ['status' => 'confirmed', 'comment' => 'Confirmée au téléphone'])
        ->assertHasNoActionErrors();

    $order->refresh();

    expect($order->status)->toBe(OrderStatus::Confirmed)
        ->and($order->confirmed_at)->not->toBeNull()
        ->and($order->statusHistories()->count())->toBe(1)
        ->and($order->statusHistories()->first()?->comment)->toBe('Confirmée au téléphone');
});

it('ne propose ni création ni édition manuelle', function () {
    $this->get('/admin/orders/create')->assertNotFound();
});
