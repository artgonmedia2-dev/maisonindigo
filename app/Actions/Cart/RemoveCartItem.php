<?php

namespace App\Actions\Cart;

use App\Models\CartItem;

class RemoveCartItem
{
    public function handle(CartItem $item): void
    {
        $item->delete();
    }
}
