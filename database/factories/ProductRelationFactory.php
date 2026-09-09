<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductRelation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductRelation>
 */
class ProductRelationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'related_id' => Product::factory(),
            'type' => ProductRelation::TYPE_COMPLETE_LOOK,
            'position' => 0,
        ];
    }
}
