<?php

namespace App\Actions\Cart;

use App\Exceptions\OutOfStockException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;

/**
 * Ajoute une variante au panier (ou augmente sa quantité), dans la limite du stock.
 * Le prix unitaire est figé à l'ajout ; il est rafraîchi au passage en caisse.
 */
class AddToCart
{
    public function handle(Cart $cart, ProductVariant $variant, int $qty = 1): CartItem
    {
        $variant->loadMissing('product');

        if (! $variant->product->isActive()) {
            throw new OutOfStockException($variant);
        }

        /** @var CartItem|null $item */
        $item = $cart->items()->where('product_variant_id', $variant->id)->first();
        $wanted = ($item->qty ?? 0) + max(1, $qty);

        if ($variant->stock < $wanted) {
            throw new OutOfStockException($variant, $variant->stock);
        }

        if ($item === null) {
            $item = $cart->items()->create([
                'product_variant_id' => $variant->id,
                'qty' => $wanted,
                'unit_price' => $variant->product->price,
            ]);
        } else {
            $item->update(['qty' => $wanted, 'unit_price' => $variant->product->price]);
        }

        $cart->touch();

        return $item;
    }
}
