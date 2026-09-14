<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCardResource;
use App\Http\Resources\ProductDetailResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function show(Request $request, Product $product): Response
    {
        abort_unless($product->isActive(), 404);

        $product->load(['variants', 'sizeChart.rows', 'media']);

        // « Complète le look » : les relations manuelles d'abord, sinon d'autres coupes du même genre.
        $related = $product->relatedProducts()->active()->with(['variants', 'media'])->limit(3)->get();

        if ($related->count() < 3) {
            $fallback = Product::query()
                ->active()
                ->where('gender', $product->gender)
                ->whereKeyNot($product->id)
                ->whereNotIn('id', $related->pluck('id'))
                ->with(['variants', 'media'])
                ->orderByDesc('is_featured')
                ->orderByDesc('is_new')
                ->limit(3 - $related->count())
                ->get();

            $related = $related->concat($fallback);
        }

        $detail = (new ProductDetailResource($product))->toArray($request);

        return Inertia::render('Product/Show', [
            'product' => $detail,
            'related' => ProductCardResource::collection($related)->toArray($request),
            'meta' => $detail['meta'],
        ]);
    }
}
