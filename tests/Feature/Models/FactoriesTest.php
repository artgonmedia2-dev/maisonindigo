<?php

use App\Enums\Cut;
use App\Enums\Gender;
use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Enums\Wash;
use App\Models\Address;
use App\Models\Admin;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Collection;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\Member;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderSequence;
use App\Models\OrderStatusHistory;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductRelation;
use App\Models\ProductVariant;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Models\SizeChart;
use App\Models\SizeChartRow;
use App\Models\StockAlert;
use App\Models\WhatsAppMessage;
use Illuminate\Database\QueryException;

it('crée chaque modèle depuis sa factory', function (string $model) {
    $instance = $model::factory()->create();

    expect($instance)->toBeInstanceOf($model)
        ->and($instance->exists)->toBeTrue()
        ->and($instance->fresh())->not->toBeNull();
})->with([
    Customer::class,
    Admin::class,
    SizeChart::class,
    SizeChartRow::class,
    Product::class,
    ProductVariant::class,
    ProductRelation::class,
    Collection::class,
    ShippingZone::class,
    ShippingRate::class,
    Address::class,
    Cart::class,
    CartItem::class,
    Order::class,
    OrderItem::class,
    OrderStatusHistory::class,
    Discount::class,
    StockAlert::class,
    Member::class,
    Page::class,
    WhatsAppMessage::class,
]);

it('construit un produit cohérent avec le nommage de la maison', function () {
    $product = Product::factory()->create([
        'gender' => Gender::Homme,
        'cut' => Cut::Straight,
        'wash' => Wash::Brut,
        'title' => 'Straight Indigo Brut',
        'slug' => 'straight-indigo-brut-homme',
        'price' => 49900,
    ]);

    expect($product->gender)->toBe(Gender::Homme)
        ->and($product->cut)->toBe(Cut::Straight)
        ->and($product->wash)->toBe(Wash::Brut)
        ->and($product->status)->toBe(ProductStatus::Active)
        ->and($product->price)->toBeInt()->toBe(49900)
        ->and($product->title)->not->toContain('Maison Indigo')
        ->and($product->skuPrefix())->toBe('MI-H-STR-BRU');
});

it('génère les variantes taille × longueur avec le SKU attendu', function () {
    $product = Product::factory()->withVariants(sizes: [30, 32])->create([
        'gender' => Gender::Femme,
        'cut' => Cut::WideLeg,
        'wash' => Wash::Stone,
    ]);

    $variants = $product->variants()->get();

    expect($variants)->toHaveCount(6)
        ->and($variants->pluck('sku')->all())->toContain('MI-F-WID-STO-32-32')
        ->and($variants->pluck('sku')->unique())->toHaveCount(6)
        ->and($variants->first()?->label())->toBe('30 / 30');
});

it('refuse deux produits identiques genre × coupe × lavage', function () {
    Product::factory()->create(['gender' => Gender::Homme, 'cut' => Cut::Slim, 'wash' => Wash::Noir, 'slug' => 'slim-noir-homme']);
    Product::factory()->create(['gender' => Gender::Homme, 'cut' => Cut::Slim, 'wash' => Wash::Noir, 'slug' => 'slim-noir-homme-2']);
})->throws(QueryException::class);

it('snapshotte l’adresse et les prix sur la commande', function () {
    $order = Order::factory()->has(OrderItem::factory()->count(2), 'items')->create();
    $order->load('items.variant.product');

    expect($order->status)->toBe(OrderStatus::New)
        ->and($order->number)->toMatch('/^MI-\d{4}-\d{6}$/')
        ->and($order->shipping_address)->toBeArray()->toHaveKeys(['name', 'phone', 'line1', 'city'])
        ->and($order->items)->toHaveCount(2)
        ->and($order->items->first()?->unit_price)->toBeInt()
        ->and($order->currency)->toBe('MAD');
});

it('formate les numéros de commande séquentiels par année', function () {
    expect(OrderSequence::formatNumber(2026, 123))->toBe('MI-2026-000123');
});

it('relie un client à ses adresses et commandes sans chargement paresseux', function () {
    $customer = Customer::factory()
        ->has(Address::factory()->count(2), 'addresses')
        ->has(Order::factory()->count(1), 'orders')
        ->create();

    $loaded = Customer::query()->with(['addresses', 'orders'])->findOrFail($customer->id);

    expect($loaded->addresses)->toHaveCount(2)
        ->and($loaded->orders)->toHaveCount(1)
        ->and($loaded->isGuest())->toBeFalse()
        ->and(Customer::factory()->guest()->create()->isGuest())->toBeTrue();
});

it('calcule les frais de livraison selon le seuil offert', function () {
    $rate = ShippingRate::factory()->create(['price' => 3500, 'free_threshold' => 60000]);

    expect($rate->costFor(49900))->toBe(3500)
        ->and($rate->costFor(60000))->toBe(0);
});
