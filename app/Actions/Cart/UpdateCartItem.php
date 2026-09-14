<?php

namespace App\Actions\Cart;

use App\Exceptions\OutOfStockException;
use App\Models\CartItem;

/**
 * Change la quantité d'une ligne. Zéro supprime la ligne.
 */
class UpdateCartItem
{
    public function handle(CartItem $item, int $qty): void
    {
        if ($qty <= 0) {
            $item->delete();

            return;
        }

        $item->loadMissing('variant');

        if ($item->variant->stock < $qty) {
            throw new OutOfStockException($item->variant, $item->variant->stock);
        }

        $item->update(['qty' => $qty]);
    }
}
