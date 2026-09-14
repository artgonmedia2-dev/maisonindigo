<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCardResource;
use App\Models\Product;
use App\Support\CatalogCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $newProducts = Cache::remember(
            CatalogCache::key('home.new'),
            now()->addMinutes(10),
            fn (): array => ProductCardResource::collection(
                Product::query()
                    ->active()
                    ->with(['variants', 'media'])
                    ->orderByDesc('is_new')
                    ->orderByDesc('is_featured')
                    ->orderByDesc('created_at')
                    ->limit(3)
                    ->get()
            )->toArray($request),
        );

        return Inertia::render('Home', [
            'meta' => [
                'title' => null,
                'description' => __('storefront.meta.home_description'),
            ],
            'newProducts' => $newProducts,
        ]);
    }
}
