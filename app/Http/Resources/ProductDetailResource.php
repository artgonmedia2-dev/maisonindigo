<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SizeChartRow;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Fiche produit : matière, mesures, variantes avec stock visible.
 *
 * @mixin Product
 */
class ProductDetailResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Product $product */
        $product = $this->resource;

        $variants = $product->variants;
        $sizes = $variants->pluck('size')->unique()->sort()->values();
        $lengths = $variants->pluck('length')->unique()->sort()->values();

        $card = (new ProductCardResource($product))->toArray($request);

        return [
            ...$card,
            'description' => $product->description,
            'fabric_origin' => $product->fabric_origin,
            'weight_oz' => $product->weight_oz === null ? null : (float) $product->weight_oz,
            'composition' => $product->composition,
            'model_height_cm' => $product->model_height_cm,
            'model_size' => $product->model_size,
            'size_advice' => $product->size_advice,
            'sizes' => $sizes->all(),
            'lengths' => $lengths->all(),
            'variants' => $variants->map(fn (ProductVariant $variant): array => [
                'id' => $variant->id,
                'size' => $variant->size,
                'length' => $variant->length,
                'sku' => $variant->sku,
                'stock' => $variant->stock,
                'in_stock' => $variant->isInStock(),
                'low_stock' => $variant->isLowStock(),
            ])->values()->all(),
            'subtitle' => app(SeoService::class)->productSubtitle($product),
            'size_chart' => $product->relationLoaded('sizeChart') && $product->sizeChart !== null
                ? $product->sizeChart->rows->map(fn (SizeChartRow $row): array => [
                    'size' => $row->size,
                    'waist_cm' => (float) $row->waist_cm,
                    'hips_cm' => (float) $row->hips_cm,
                    'thigh_cm' => (float) $row->thigh_cm,
                    'inseam_30' => (float) $row->inseam_30,
                    'inseam_32' => (float) $row->inseam_32,
                    'inseam_34' => (float) $row->inseam_34,
                ])->values()->all()
                : [],
            'images' => ProductImageResource::all($product),
            'meta' => [
                'title' => $product->meta_title ?? "{$product->title} {$product->gender->getLabel()}",
                'description' => $product->meta_description ?? $product->description,
            ],
        ];
    }
}
