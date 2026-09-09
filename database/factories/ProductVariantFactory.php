<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'size' => fake()->randomElement(ProductVariant::SIZES),
            'length' => fake()->randomElement(ProductVariant::LENGTHS),
            'sku' => null,
            'stock' => fake()->numberBetween(0, 20),
            'low_stock_threshold' => 3,
            'position' => 0,
        ];
    }

    public function configure(): static
    {
        // Le SKU dérive du produit : MI-{genre}-{coupe}-{lavage}-{taille}-{longueur}.
        return $this->afterMaking(function (ProductVariant $variant): void {
            if ($variant->sku === null) {
                $product = $variant->product ?? Product::find($variant->product_id);

                if ($product !== null) {
                    $variant->sku = ProductVariant::buildSku($product, $variant->size, $variant->length);
                }
            }
        });
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => ['stock' => 0]);
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => ['stock' => 2, 'low_stock_threshold' => 3]);
    }
}
