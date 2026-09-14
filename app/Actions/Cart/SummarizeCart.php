<?php

namespace App\Actions\Cart;

use App\Http\Resources\ProductImageResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Services\DiscountEngine;
use App\Services\ShippingCalculator;

/**
 * Résumé du panier partagé avec toutes les pages Inertia (tiroir, page panier, caisse).
 * Montants en centimes entiers.
 */
class SummarizeCart
{
    public function __construct(
        private readonly DiscountEngine $discounts,
        private readonly ShippingCalculator $shipping,
    ) {}

    /**
     * @return array{
     *     count: int,
     *     subtotal: int,
     *     discount: array{total: int, label: string|null, code: string|null, code_error: string|null},
     *     total: int,
     *     free_shipping_threshold: int|null,
     *     free_shipping_remaining: int|null,
     *     items: list<array<string, mixed>>
     * }
     */
    public function handle(?Cart $cart, ?string $code = null): array
    {
        if ($cart === null) {
            return $this->empty();
        }

        $cart->loadMissing('items.variant.product.media');

        $items = $cart->items
            ->filter(fn (CartItem $item): bool => $item->variant !== null && $item->variant->product !== null)
            ->values();

        $subtotal = (int) $items->sum(fn (CartItem $item): int => $item->total());
        $quantity = (int) $items->sum('qty');
        $discount = $this->discounts->compute($subtotal, $quantity, $code);
        $threshold = $this->shipping->freeThreshold();
        $afterDiscount = $subtotal - $discount->total;

        return [
            'count' => $quantity,
            'subtotal' => $subtotal,
            'discount' => $discount->toArray(),
            'total' => $afterDiscount,
            'free_shipping_threshold' => $threshold,
            'free_shipping_remaining' => $threshold === null ? null : max(0, $threshold - $afterDiscount),
            'items' => $items->map(fn (CartItem $item): array => $this->line($item))->all(),
        ];
    }

    /**
     * @return array{count: int, subtotal: int, discount: array{total: int, label: string|null, code: string|null, code_error: string|null}, total: int, free_shipping_threshold: int|null, free_shipping_remaining: int|null, items: list<array<string, mixed>>}
     */
    public function empty(): array
    {
        $threshold = $this->shipping->freeThreshold();

        return [
            'count' => 0,
            'subtotal' => 0,
            'discount' => ['total' => 0, 'label' => null, 'code' => null, 'code_error' => null],
            'total' => 0,
            'free_shipping_threshold' => $threshold,
            'free_shipping_remaining' => $threshold,
            'items' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function line(CartItem $item): array
    {
        $variant = $item->variant;
        $product = $variant->product;

        return [
            'id' => $item->id,
            'variant_id' => $variant->id,
            'product_id' => $product->id,
            'slug' => $product->slug,
            'title' => $product->title,
            'gender' => $product->gender->getLabel(),
            'size' => $variant->size,
            'length' => $variant->length,
            'sku' => $variant->sku,
            'qty' => $item->qty,
            'unit_price' => $item->unit_price,
            'total' => $item->total(),
            'stock' => $variant->stock,
            'image' => ProductImageResource::first($product),
        ];
    }
}
