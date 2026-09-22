<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\Gender;
use App\Enums\HomeSlot;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCardResource;
use App\Models\Collection;
use App\Models\Product;
use App\Settings\ShopSettings;
use App\Support\CatalogCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
     * @return array{first: bool, kicker: string, title: string, women: array{title: string, text: string, count: int, image: string|null, badge: string|null}, men: array{title: string, text: string, count: int, image: string|null, badge: string|null}}
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

        // Le visuel et le badge viennent de la collection mise en avant :
        // une seule fiche à tenir à jour, dans Catalogue → Collections.
        $mises = Collection::query()
            ->whereNotNull('home_slot')
            ->with('media')
            ->get()
            ->keyBy(fn (Collection $collection): string => $collection->home_slot->value);

        return [
            'first' => $settings->home_collections_first,
            'kicker' => $settings->home_collections_kicker,
            'title' => $settings->home_collections_title,
            'women' => [
                'title' => $settings->home_women_title,
                'text' => $settings->home_women_text,
                'count' => $counts[Gender::Femme->value] ?? 0,
                ...$this->visual($mises, HomeSlot::Women),
            ],
            'men' => [
                'title' => $settings->home_men_title,
                'text' => $settings->home_men_text,
                'count' => $counts[Gender::Homme->value] ?? 0,
                ...$this->visual($mises, HomeSlot::Men),
            ],
        ];
    }

    /**
     * Le visuel et le badge de la collection placée sur cet emplacement.
     * Sans collection, ou sans image téléversée, la carte revient au gabarit
     * denim : jamais de cadre vide.
     *
     * @param  \Illuminate\Support\Collection<string, Collection>  $mises
     * @return array{image: string|null, badge: string|null}
     */
    private function visual(\Illuminate\Support\Collection $mises, HomeSlot $slot): array
    {
        $collection = $mises->get($slot->value);

        if ($collection === null) {
            return ['image' => null, 'badge' => null];
        }

        $media = $collection->getFirstMedia(Collection::MEDIA_COVER);

        return [
            'image' => $media === null ? null : $this->mediaUrl($media),
            'badge' => $collection->home_badge,
        ];
    }

    /**
     * La conversion allégée quand elle existe vraiment sur le disque, sinon
     * l'original : une file de conversions interrompue ne casse pas l'accueil.
     */
    private function mediaUrl(Media $media): ?string
    {
        if ($media->hasGeneratedConversion('cover') && is_file($media->getPath('cover'))) {
            return $media->getUrl('cover');
        }

        return is_file($media->getPath()) ? $media->getUrl() : null;
    }
}
