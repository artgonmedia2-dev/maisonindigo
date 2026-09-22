<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\Gender;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCardResource;
use App\Models\Product;
use App\Settings\ShopSettings;
use App\Support\CatalogCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(Request $request, ShopSettings $settings): Response
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
            'collections' => $this->collections($settings),
        ]);
    }

    /**
     * La section « Deux collections » : textes réglés depuis le back-office et
     * nombre de modèles en ligne, qui rassure avant le clic.
     *
     * @return array{first: bool, kicker: string, title: string, women: array{title: string, text: string, count: int}, men: array{title: string, text: string, count: int}}
     */
    private function collections(ShopSettings $settings): array
    {
        /** @var array<string, int> $counts */
        $counts = Cache::remember(
            CatalogCache::key('home.gender_counts'),
            now()->addMinutes(10),
            fn (): array => Product::query()
                ->active()
                ->selectRaw('gender, count(*) as total')
                ->groupBy('gender')
                ->pluck('total', 'gender')
                ->map(fn ($total): int => (int) $total)
                ->all(),
        );

        return [
            'first' => $settings->home_collections_first,
            'kicker' => $settings->home_collections_kicker,
            'title' => $settings->home_collections_title,
            'women' => [
                'title' => $settings->home_women_title,
                'text' => $settings->home_women_text,
                'count' => $counts[Gender::Femme->value] ?? 0,
            ],
            'men' => [
                'title' => $settings->home_men_title,
                'text' => $settings->home_men_text,
                'count' => $counts[Gender::Homme->value] ?? 0,
            ],
        ];
    }
}
