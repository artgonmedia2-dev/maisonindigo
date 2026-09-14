<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Jobs\SendWhatsAppMessage;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Database\Seeders\ShippingZonesSeeder;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(ShippingZonesSeeder::class);
    Queue::fake();
});

function cartWith(int $qty = 1, int $stock = 5, int $price = 49900): ProductVariant
{
    $product = Product::factory()->create(['price' => $price]);
    $variant = ProductVariant::factory()->for($product)->create(['size' => 32, 'length' => 32, 'stock' => $stock]);

    test()->post(route('cart.store'), ['variant_id' => $variant->id, 'qty' => $qty]);

    return $variant;
}

/**
 * @return array<string, mixed>
 */
function checkoutPayload(array $overrides = []): array
{
    return [
        'name' => 'Salma Idrissi',
        'phone' => '06 12 34 56 78',
        'email' => 'salma@exemple.ma',
        'line1' => '12 rue des Orangers',
        'line2' => 'Appartement 4',
        'city' => 'Casablanca',
        'region' => '',
        'payment_method' => 'cod',
        'discount_code' => '',
        'notes' => 'Interphone Idrissi',
        'accept_terms' => true,
        ...$overrides,
    ];
}

it('redirige vers le panier quand il est vide', function () {
    $this->get(route('checkout.show'))->assertRedirect(route('cart.index'));
});

it('affiche la page de commande avec zones, villes et modes de paiement', function () {
    cartWith();

    $this->get(route('checkout.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Checkout/Show')
            ->has('zones', 6)
            ->has('cities')
            ->has('payment_methods', 2)
            ->where('payment_methods.0.value', 'cod')
            ->where('cart.count', 1)
        );
});

it('enregistre une commande payée à la livraison', function () {
    $variant = cartWith();

    $response = $this->post(route('checkout.store'), checkoutPayload());

    $order = Order::query()->firstOrFail();

    $response->assertRedirect(route('checkout.confirmation', $order));

    expect($order->number)->toBe('MI-'.date('Y').'-000001')
        ->and($order->status)->toBe(OrderStatus::New)
        ->and($order->payment_method)->toBe(PaymentMethod::Cod)
        ->and($order->subtotal)->toBe(49900)
        ->and($order->discount_total)->toBe(0)
        ->and($order->shipping_total)->toBe(3500)
        ->and($order->total)->toBe(53400)
        ->and($order->currency)->toBe('MAD')
        ->and($order->shipping_address['name'])->toBe('Salma Idrissi')
        ->and($order->shipping_address['phone'])->toBe('0612345678')
        ->and($order->shipping_address['zone'])->toBe('Casablanca')
        ->and($order->customer_notes)->toBe('Interphone Idrissi')
        ->and($order->items)->toHaveCount(1)
        ->and($order->items[0]->sku)->toBe($variant->sku)
        ->and($order->items[0]->unit_price)->toBe(49900)
        ->and($order->statusHistories)->toHaveCount(1)
        ->and($order->statusHistories[0]->to_status)->toBe(OrderStatus::New);

    expect($variant->refresh()->stock)->toBe(4)
        ->and(CartItem::query()->count())->toBe(0);

    Queue::assertPushed(SendWhatsAppMessage::class, fn (SendWhatsAppMessage $job) => $job->order->is($order) && $job->type === 'cod_confirmation');

    $this->get(route('checkout.confirmation', $order))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Checkout/Confirmation')
            ->where('order.number', $order->number)
            ->where('order.payment_method', 'cod')
            ->where('order.total', 53400)
            ->has('order.items', 1)
            ->where('cart.count', 0)
        );
});

it('n’envoie pas de WhatsApp pour un virement', function () {
    cartWith();

    $this->post(route('checkout.store'), checkoutPayload(['payment_method' => 'transfer']));

    expect(Order::query()->firstOrFail()->payment_method)->toBe(PaymentMethod::Transfer);
    Queue::assertNothingPushed();
});

it('offre la livraison à partir de 600 dh et applique la remise automatique', function () {
    Discount::factory()->bundle()->create();
    cartWith(qty: 2);

    $this->post(route('checkout.store'), checkoutPayload());

    $order = Order::query()->firstOrFail();

    expect($order->subtotal)->toBe(99800)
        ->and($order->discount_total)->toBe(9980)
        ->and($order->shipping_total)->toBe(0)
        ->and($order->total)->toBe(89820);
});

it('applique le tarif de repli pour une ville inconnue', function () {
    cartWith();

    $this->post(route('checkout.store'), checkoutPayload(['city' => 'Zagora']));

    $order = Order::query()->firstOrFail();

    expect($order->shipping_total)->toBe(4900)
        ->and($order->shipping_address['zone'])->toBe('Autres villes');
});

it('applique un code saisi à la caisse', function () {
    Discount::factory()->create(['code' => 'INDIGO10', 'value' => 10]);
    cartWith();

    $this->get(route('checkout.show', ['code' => 'indigo10']))
        ->assertInertia(fn (Assert $page) => $page->where('cart.discount.code', 'INDIGO10')->where('cart.discount.total', 4990));

    $this->post(route('checkout.store'), checkoutPayload());

    $order = Order::query()->firstOrFail();

    expect($order->discount_code)->toBe('INDIGO10')
        ->and($order->discount_total)->toBe(4990)
        ->and(Discount::query()->firstOrFail()->usage_count)->toBe(1);
});

it('valide le formulaire', function () {
    cartWith();

    $this->post(route('checkout.store'), checkoutPayload(['phone' => '12345', 'name' => '', 'accept_terms' => false, 'payment_method' => 'card']))
        ->assertSessionHasErrors(['phone', 'name', 'accept_terms', 'payment_method']);

    expect(Order::query()->count())->toBe(0);
});

it('refuse une commande dont le stock a disparu entre-temps', function () {
    $variant = cartWith();
    $variant->update(['stock' => 0]);

    $this->post(route('checkout.store'), checkoutPayload())
        ->assertRedirect(route('cart.index'))
        ->assertSessionHas('error');

    expect(Order::query()->count())->toBe(0);
});

it('rattache la commande au client connecté', function () {
    $customer = Customer::factory()->create();
    $this->actingAs($customer);
    cartWith();

    $this->post(route('checkout.store'), checkoutPayload());

    expect(Order::query()->firstOrFail()->customer_id)->toBe($customer->id);
});

it('cache la confirmation à une autre session', function () {
    cartWith();
    $this->post(route('checkout.store'), checkoutPayload());
    $order = Order::query()->firstOrFail();

    $this->flushSession();

    $this->get(route('checkout.confirmation', $order))->assertNotFound();
});

it('numérote les commandes en séquence', function () {
    cartWith();
    $this->post(route('checkout.store'), checkoutPayload());
    cartWith();
    $this->post(route('checkout.store'), checkoutPayload());

    expect(Order::query()->orderBy('id')->pluck('number')->all())
        ->toBe(['MI-'.date('Y').'-000001', 'MI-'.date('Y').'-000002']);
});
