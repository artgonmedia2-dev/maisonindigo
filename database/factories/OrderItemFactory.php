<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $qty = 1;
        $unitPrice = 49900;

        return [
            'order_id' => Order::factory(),
            'product_variant_id' => ProductVariant::factory(),
            'title' => 'Straight Indigo Brut',
            'size' => 32,
            'length' => 32,
            'sku' => 'MI-H-STR-BRU-32-32',
            'qty' => $qty,
            'unit_price' => $unitPrice,
            'total' => $qty * $unitPrice,
        ];
    }

    public function configure(): static
    {
        // Snapshot cohérent avec la variante réellement associée.
        return $this->afterMaking(function (OrderItem $item): void {
            $variant = $item->variant;

            if ($variant !== null) {
                $product = $variant->product;
                $item->title = $product->title;
                $item->size = $variant->size;
                $item->length = $variant->length;
                $item->sku = $variant->sku;
                $item->unit_price = $product->price;
                $item->total = $item->qty * $item->unit_price;
            }
        });
    }
}
