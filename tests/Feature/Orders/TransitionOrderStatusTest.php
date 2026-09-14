<?php

use App\Actions\Orders\GenerateOrderNumber;
use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\OrderStatus;
use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderSequence;
use App\Models\ProductVariant;

it('génère des numéros séquentiels par année', function () {
    $generate = app(GenerateOrderNumber::class);

    expect($generate->handle(2026))->toBe('MI-2026-000001')
        ->and($generate->handle(2026))->toBe('MI-2026-000002')
        ->and($generate->handle(2027))->toBe('MI-2027-000001')
        ->and(OrderSequence::query()->where('year', 2026)->value('last_number'))->toBe(2);
});

it('valide la transition, horodate et écrit l’historique', function () {
    $order = Order::factory()->create();
    $admin = Admin::factory()->create();

    $order = app(TransitionOrderStatus::class)->handle($order, OrderStatus::Confirmed, $admin, 'Confirmée par WhatsApp');

    expect($order->status)->toBe(OrderStatus::Confirmed)
        ->and($order->confirmed_at)->not->toBeNull()
        ->and($order->statusHistories)->toHaveCount(1)
        ->and($order->statusHistories[0]->from_status)->toBe(OrderStatus::New)
        ->and($order->statusHistories[0]->to_status)->toBe(OrderStatus::Confirmed)
        ->and($order->statusHistories[0]->admin_id)->toBe($admin->id)
        ->and($order->statusHistories[0]->comment)->toBe('Confirmée par WhatsApp');
});

it('refuse une transition interdite', function () {
    $order = Order::factory()->create();

    app(TransitionOrderStatus::class)->handle($order, OrderStatus::Shipped);
})->throws(InvalidStatusTransitionException::class);

it('restitue le stock à l’annulation', function () {
    $variant = ProductVariant::factory()->create(['stock' => 3]);
    $order = Order::factory()->create();
    OrderItem::factory()->for($order)->create(['product_variant_id' => $variant->id, 'qty' => 2]);

    app(TransitionOrderStatus::class)->handle($order, OrderStatus::Cancelled);

    expect($variant->refresh()->stock)->toBe(5);
});

it('suit le parcours complet jusqu’à la livraison', function () {
    $order = Order::factory()->create();
    $transition = app(TransitionOrderStatus::class);

    foreach ([OrderStatus::Confirmed, OrderStatus::Prepared, OrderStatus::Shipped, OrderStatus::Delivered] as $status) {
        $order = $transition->handle($order, $status);
    }

    expect($order->status)->toBe(OrderStatus::Delivered)
        ->and($order->shipped_at)->not->toBeNull()
        ->and($order->delivered_at)->not->toBeNull()
        ->and($order->statusHistories)->toHaveCount(4);
});
