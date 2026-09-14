<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Cart\ResolveCart;
use App\Actions\Cart\SummarizeCart;
use App\Actions\Checkout\PlaceOrder;
use App\Data\CheckoutData;
use App\Enums\PaymentMethod;
use App\Exceptions\OutOfStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\CheckoutRequest;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShippingZone;
use App\Services\ShippingCalculator;
use App\Settings\ShopSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Commande en une page, invité par défaut. Paiement à la livraison ou virement.
 */
class CheckoutController extends Controller
{
    public const SESSION_ORDERS = 'placed_orders';

    public const SESSION_CODE = 'discount_code';

    public function __construct(
        private readonly ResolveCart $resolveCart,
        private readonly SummarizeCart $summarizeCart,
        private readonly ShippingCalculator $shipping,
    ) {}

    public function show(Request $request): Response|RedirectResponse
    {
        $cart = $this->resolveCart->handle($request);

        if ($cart === null || $this->summarizeCart->handle($cart)['count'] === 0) {
            return redirect()->route('cart.index');
        }

        // Un code saisi est gardé en session : le résumé partagé l'applique à chaque page.
        if ($request->has('code')) {
            $code = mb_strtoupper(trim((string) $request->query('code')));
            $code === ''
                ? $request->session()->forget(self::SESSION_CODE)
                : $request->session()->put(self::SESSION_CODE, $code);
        }

        /** @var Customer|null $customer */
        $customer = $request->user();
        /** @var Address|null $address */
        $address = $customer === null ? null : $customer->addresses()->orderByDesc('is_default')->first();

        return Inertia::render('Checkout/Show', [
            'meta' => ['title' => __('storefront.checkout.title'), 'description' => null],
            'zones' => $this->shipping->zones()->map(fn (ShippingZone $zone): array => [
                'id' => $zone->id,
                'name' => $zone->name,
                'cities' => $zone->cities,
                'delay' => $zone->delay,
                'price' => $zone->rate === null ? 0 : $zone->rate->price,
                'free_threshold' => $zone->rate === null ? null : $zone->rate->free_threshold,
            ])->values()->all(),
            'cities' => $this->shipping->cities(),
            'payment_methods' => array_map(fn (PaymentMethod $method): array => [
                'value' => $method->value,
                'label' => $method->getLabel(),
                'description' => __("storefront.checkout.payment.{$method->value}"),
            ], PaymentMethod::cases()),
            'prefill' => [
                'name' => $address->name ?? $customer->name ?? '',
                'phone' => $address->phone ?? $customer->phone ?? '',
                'email' => $customer->email ?? '',
                'line1' => $address->line1 ?? '',
                'line2' => $address->line2 ?? '',
                'city' => $address->city ?? '',
                'region' => $address->region ?? '',
            ],
        ]);
    }

    public function store(CheckoutRequest $request, PlaceOrder $placeOrder): RedirectResponse
    {
        $cart = $this->resolveCart->handle($request);

        if ($cart === null) {
            return redirect()->route('cart.index');
        }

        /** @var Customer|null $customer */
        $customer = $request->user();

        $validated = $request->validated();

        if (empty($validated['discount_code'])) {
            $validated['discount_code'] = $request->session()->get(self::SESSION_CODE);
        }

        try {
            $order = $placeOrder->handle($cart, CheckoutData::fromValidated($validated), $customer);
        } catch (OutOfStockException $exception) {
            return redirect()->route('cart.index')->with('error', $exception->getMessage());
        }

        $this->resolveCart->forget($request);
        $request->session()->forget(self::SESSION_CODE);

        /** @var list<string> $placed */
        $placed = $request->session()->get(self::SESSION_ORDERS, []);
        $request->session()->put(self::SESSION_ORDERS, [...$placed, $order->number]);

        return redirect()->route('checkout.confirmation', $order);
    }

    public function confirmation(Request $request, Order $order, ShopSettings $settings): Response
    {
        /** @var list<string> $placed */
        $placed = $request->session()->get(self::SESSION_ORDERS, []);
        $ownsOrder = $request->user() !== null && $order->customer_id === $request->user()->getAuthIdentifier();

        abort_unless(in_array($order->number, $placed, true) || $ownsOrder, 404);

        $order->load('items');

        return Inertia::render('Checkout/Confirmation', [
            'meta' => ['title' => __('storefront.checkout.confirmation_title'), 'description' => null],
            'order' => [
                'number' => $order->number,
                'status' => $order->status->value,
                'status_label' => $order->status->getLabel(),
                'payment_method' => $order->payment_method->value,
                'payment_label' => $order->payment_method->getLabel(),
                'subtotal' => $order->subtotal,
                'discount_total' => $order->discount_total,
                'shipping_total' => $order->shipping_total,
                'total' => $order->total,
                'address' => $order->shipping_address,
                'created_at' => $order->created_at?->toIso8601String(),
                'items' => $order->items->map(fn (OrderItem $item): array => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'size' => $item->size,
                    'length' => $item->length,
                    'sku' => $item->sku,
                    'qty' => $item->qty,
                    'unit_price' => $item->unit_price,
                    'total' => $item->total,
                ])->values()->all(),
            ],
            'bank' => [
                'holder' => $settings->bank_holder,
                'iban' => $settings->bank_iban,
                'bank' => $settings->bank_name,
            ],
        ]);
    }
}
