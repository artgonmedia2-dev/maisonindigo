<?php

namespace App\Actions\Checkout;

use App\Actions\Orders\GenerateOrderNumber;
use App\Data\CheckoutData;
use App\Enums\OrderStatus;
use App\Exceptions\OutOfStockException;
use App\Jobs\SendWhatsAppMessage;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Services\DiscountEngine;
use App\Services\ShippingCalculator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Transforme un panier en commande, dans une transaction :
 * vérification du stock, numéro séquentiel, lignes et adresse figées,
 * décrément du stock, vidage du panier, puis confirmation WhatsApp en job (COD).
 */
class PlaceOrder
{
    public function __construct(
        private readonly GenerateOrderNumber $generateNumber,
        private readonly DiscountEngine $discounts,
        private readonly ShippingCalculator $shipping,
    ) {}

    public function handle(Cart $cart, CheckoutData $data, ?Customer $customer = null): Order
    {
        $cart->loadMissing('items.variant.product');

        if ($cart->items->isEmpty()) {
            throw new InvalidArgumentException('Le panier est vide.');
        }

        $order = DB::transaction(function () use ($cart, $data, $customer): Order {
            $variants = ProductVariant::query()
                ->with('product')
                ->whereIn('id', $cart->items->pluck('product_variant_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lines = [];
            $subtotal = 0;
            $quantity = 0;

            foreach ($cart->items as $item) {
                /** @var ProductVariant|null $variant */
                $variant = $variants->get($item->product_variant_id);

                if ($variant === null || ! $variant->product->isActive()) {
                    throw new OutOfStockException($item->variant);
                }

                if ($variant->stock < $item->qty) {
                    throw new OutOfStockException($variant, $variant->stock);
                }

                $unitPrice = $variant->product->price;
                $lines[] = [
                    'product_variant_id' => $variant->id,
                    'title' => $variant->product->title,
                    'size' => $variant->size,
                    'length' => $variant->length,
                    'sku' => $variant->sku,
                    'qty' => $item->qty,
                    'unit_price' => $unitPrice,
                    'total' => $unitPrice * $item->qty,
                ];
                $subtotal += $unitPrice * $item->qty;
                $quantity += $item->qty;
            }

            $discount = $this->discounts->compute($subtotal, $quantity, $data->discountCode);
            $quote = $this->shipping->quote($data->city, $subtotal - $discount->total);
            $total = $subtotal - $discount->total + $quote->cost;

            $order = Order::query()->create([
                'number' => $this->generateNumber->handle(),
                'customer_id' => $customer?->id,
                'status' => OrderStatus::New,
                'payment_method' => $data->paymentMethod,
                'subtotal' => $subtotal,
                'discount_total' => $discount->total,
                'shipping_total' => $quote->cost,
                'total' => $total,
                'currency' => 'MAD',
                'shipping_address' => $data->toSnapshot($quote->zoneName),
                'discount_code' => $discount->code,
                'customer_notes' => $data->notes,
            ]);

            $order->items()->createMany($lines);

            foreach ($lines as $line) {
                ProductVariant::query()->whereKey($line['product_variant_id'])->decrement('stock', $line['qty']);
            }

            if ($discount->discount !== null) {
                $discount->discount->increment('usage_count');
            }

            $order->statusHistories()->create([
                'from_status' => null,
                'to_status' => OrderStatus::New,
                'comment' => __('storefront.checkout.history_created', ['method' => $data->paymentMethod->getLabel()]),
            ]);

            $cart->items()->each(fn (CartItem $item) => $item->delete());
            $cart->delete();

            return $order;
        });

        if ($order->isCod()) {
            SendWhatsAppMessage::dispatch($order)->afterCommit();
        }

        return $order->load('items');
    }
}
