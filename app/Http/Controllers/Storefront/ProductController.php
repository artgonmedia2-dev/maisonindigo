<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\Gender;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCardResource;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductImageResource;
use App\Models\Product;
use App\Services\DiscountEngine;
use App\Services\SeoService;
use App\Support\CatalogTerms;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * /{genre}/{slug} : le genre porte l'adresse, le slug ne le répète plus.
     */
    public function show(Request $request, SeoService $seo, string $genre, string $slug): Response
    {
        $gender = Gender::tryFrom($genre);
        abort_if($gender === null, 404);

        $product = Product::query()
            ->where('gender', $gender)
            ->where('slug', $slug)
            ->firstOrFail();

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
        $breadcrumb = $this->breadcrumb($product);

        return Inertia::render('Product/Show', [
            'product' => $detail,
            'related' => ProductCardResource::collection($related)->toArray($request),
            'meta' => $detail['meta'],
            'seo' => $seo->forProduct($product, $breadcrumb),
            'breadcrumb' => $breadcrumb,
            // Le hub de la coupe : chaque fiche y renvoie, aucune page orpheline.
            'hub' => $this->hubLink($product),
            // « Coloris — même coupe » : les frères du produit, même genre et
            // même coupe, un par lavage.
            'siblings' => $this->siblings($product),
            'pack' => app(DiscountEngine::class)->packOffer($product->price),
        ]);
    }

    /**
     * Les autres lavages de la même coupe, le produit courant compris.
     *
     * C'est le sélecteur de coloris : le client change de lavage sans perdre
     * sa coupe, et chaque lavage garde son adresse propre.
     *
     * @return list<array{slug: string, url: string, label: string, image: string|null, current: bool}>
     */
    private function siblings(Product $product): array
    {
        $freres = Product::query()
            ->active()
            ->where('gender', $product->gender)
            ->where('cut', $product->cut)
            ->with('media')
            ->orderBy('id')
            ->get();

        if ($freres->count() < 2) {
            return [];
        }

        return $freres
            ->map(fn (Product $frere): array => [
                'slug' => $frere->slug,
                'url' => $frere->path(),
                'label' => $frere->washLabel(),
                'image' => ProductImageResource::first($frere)['src'] ?? null,
                'current' => $frere->is($product),
            ])
            ->values()
            ->all();
    }

    /**
     * L'ancienne adresse /produit/{slug} renvoie vers la nouvelle, en 301 :
     * les liens déjà partagés gardent leur valeur.
     */
    public function legacy(string $slug): RedirectResponse
    {
        $product = Product::query()->where('slug', $slug)->firstOrFail();

        return redirect()->to($product->path(), 301);
    }

    /**
     * @return list<array{name: string, url: string}>
     */
    private function breadcrumb(Product $product): array
    {
        $base = rtrim((string) config('app.url'), '/');
        $cut = app(CatalogTerms::class)->cut($product->cut);

        $trail = [
            ['name' => __('seo.breadcrumb.home'), 'url' => $base.'/'],
            ['name' => $product->gender->getLabel(), 'url' => $base.'/'.$product->gender->value],
        ];

        if ($cut !== null) {
            $trail[] = [
                'name' => trim(__('seo.hub.heading', ['cut' => Str::lower($cut->name), 'gender' => ''])),
                'url' => $base."/{$product->gender->value}/jean-{$cut->urlSegment()}",
            ];
        }

        $trail[] = ['name' => $product->title, 'url' => $base.$product->path()];

        return $trail;
    }

    /**
     * @return array{label: string, url: string}|null
     */
    private function hubLink(Product $product): ?array
    {
        $cut = app(CatalogTerms::class)->cut($product->cut);

        if ($cut === null || ! $cut->is_active) {
            return null;
        }

        return [
            'label' => __('seo.hub.heading', ['cut' => Str::lower($cut->name), 'gender' => $product->gender->value]),
            'url' => "/{$product->gender->value}/jean-{$cut->urlSegment()}",
        ];
    }
}
