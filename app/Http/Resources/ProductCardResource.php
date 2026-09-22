<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Carte produit des grilles : titre « Coupe Lavage », sous-titre genre · matière · tailles.
 *
 * @mixin Product
 */
class ProductCardResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Product $product */
        $product = $this->resource;

        $variants = $product->relationLoaded('variants') ? $product->variants : collect();
        $sizes = $variants->pluck('size')->unique()->sort()->values();
        $inStock = $variants->contains(fn (ProductVariant $variant): bool => $variant->stock > 0);

        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'title' => $product->title,
            'gender' => $product->gender->value,
            'gender_label' => $product->gender->getLabel(),
            'cut' => $product->cut,
            'cut_label' => $product->cutLabel(),
            'wash' => $product->wash,
            'wash_label' => $product->washLabel(),
            'subtitle' => self::subtitle($product, $sizes->first(), $sizes->last()),
            'price' => $product->price,
            'compare_at_price' => $product->compare_at_price,
            'patch' => self::patch($product),
            'in_stock' => $inStock,
            'image' => ProductImageResource::first($product),
            'url' => $product->path(),
        ];
    }

    public static function subtitle(Product $product, ?int $minSize, ?int $maxSize): string
    {
        $parts = [$product->gender->getLabel()];

        if ($product->fabric_origin !== null) {
            $weight = $product->weight_oz !== null ? ' '.rtrim(rtrim(number_format((float) $product->weight_oz, 1, ',', ''), '0'), ',').' oz' : '';
            $parts[] = mb_strtolower($product->fabric_origin).$weight;
        }

        if ($minSize !== null && $maxSize !== null) {
            $parts[] = "{$minSize} – {$maxSize}";
        }

        return implode(' · ', $parts);
    }

    public static function patch(Product $product): ?string
    {
        return match (true) {
            $product->is_atelier => 'atelier',
            $product->is_new => 'new',
            default => null,
        };
    }
}
