<?php

namespace App\Exceptions;

use App\Models\ProductVariant;
use RuntimeException;

/**
 * Stock insuffisant pour une variante. Le message est prêt à être affiché au client.
 */
class OutOfStockException extends RuntimeException
{
    public function __construct(
        public readonly ProductVariant $variant,
        public readonly int $available = 0,
    ) {
        $variant->loadMissing('product');

        $message = $available > 0
            ? __('storefront.cart.stock_limited', ['title' => $variant->product->title, 'size' => $variant->label(), 'count' => $available])
            : __('storefront.cart.out_of_stock', ['title' => $variant->product->title, 'size' => $variant->label()]);

        parent::__construct($message);
    }
}
