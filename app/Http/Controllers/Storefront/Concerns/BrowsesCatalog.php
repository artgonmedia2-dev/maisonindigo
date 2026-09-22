<?php

namespace App\Http\Controllers\Storefront\Concerns;

use App\Enums\Gender;
use App\Models\Cut;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Wash;
use App\Support\CatalogTerms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Lecture des facettes et filtrage d'une grille produits.
 *
 * Partagé par les collections, les hubs de coupe et les sous-collections
 * lavage : une seule définition des facettes, donc une seule définition de
 * ce qui est indexable. Taille, longueur, prix et tri restent des paramètres
 * d'URL, jamais des adresses propres.
 */
trait BrowsesCatalog
{
    /** @var list<string> */
    public static array $sorts = ['new', 'price_asc', 'price_desc'];

    /**
     * @return array{cut: list<string>, wash: list<string>, size: int|null, length: int|null, sort: string}
     */
    protected function catalogFilters(Request $request): array
    {
        $termes = app(CatalogTerms::class);

        $cuts = array_values(array_filter(
            (array) $request->query('cut', []),
            fn ($value): bool => is_string($value) && $termes->cut($value) !== null,
        ));

        $washes = array_values(array_filter(
            (array) $request->query('wash', []),
            fn ($value): bool => is_string($value) && $termes->wash($value) !== null,
        ));

        $size = (int) $request->query('size', '0');
        $length = (int) $request->query('length', '0');
        $sort = (string) $request->query('sort', 'new');

        return [
            'cut' => array_map('strval', $cuts),
            'wash' => array_map('strval', $washes),
            'size' => in_array($size, ProductVariant::SIZES, true) ? $size : null,
            'length' => in_array($length, ProductVariant::LENGTHS, true) ? $length : null,
            'sort' => in_array($sort, self::$sorts, true) ? $sort : 'new',
        ];
    }

    /**
     * Vrai dès qu'une facette non indexable est active. La page passe alors
     * en « noindex, follow » avec canonical vers l'adresse propre : c'est ce
     * qui empêche une grille filtrée de cannibaliser son hub.
     *
     * @param  array{cut: list<string>, wash: list<string>, size: int|null, length: int|null, sort: string}  $filters
     */
    protected function isFaceted(array $filters, bool $washIsPath = false): bool
    {
        return $filters['size'] !== null
            || $filters['length'] !== null
            || $filters['sort'] !== 'new'
            || $filters['cut'] !== []
            || (! $washIsPath && $filters['wash'] !== []);
    }

    /**
     * @param  Builder<Product>  $query
     * @param  array{cut: list<string>, wash: list<string>, size: int|null, length: int|null, sort: string}  $filters
     * @return Builder<Product>
     */
    protected function applyCatalogFilters(Builder $query, array $filters): Builder
    {
        $query
            ->when($filters['cut'] !== [], fn (Builder $q) => $q->whereIn('cut', $filters['cut']))
            ->when($filters['wash'] !== [], fn (Builder $q) => $q->whereIn('wash', $filters['wash']))
            ->when($filters['size'] !== null, fn (Builder $q) => $q->whereHas(
                'variants',
                fn (Builder $variants) => $variants->where('size', $filters['size'])->where('stock', '>', 0),
            ))
            ->when($filters['length'] !== null, fn (Builder $q) => $q->whereHas(
                'variants',
                fn (Builder $variants) => $variants->where('length', $filters['length'])->where('stock', '>', 0),
            ));

        match ($filters['sort']) {
            'price_asc' => $query->orderBy('price')->orderBy('id'),
            'price_desc' => $query->orderByDesc('price')->orderBy('id'),
            default => $query->orderByDesc('is_new')->orderByDesc('created_at')->orderBy('id'),
        };

        return $query;
    }

    /**
     * Les facettes réellement disponibles dans la grille courante : on ne
     * propose jamais un filtre qui ne renverrait rien.
     *
     * @param  Builder<Product>  $base
     * @return array{cuts: list<array{value: string, label: string}>, washes: list<array{value: string, label: string}>, sizes: list<int>, lengths: list<int>, sorts: list<array{value: string, label: string}>}
     */
    protected function catalogFacets(Builder $base, ?Gender $gender): array
    {
        $termes = app(CatalogTerms::class);

        $presentCuts = (clone $base)->distinct()->pluck('cut')->map(fn ($cut): string => (string) $cut);
        $presentWashes = (clone $base)->distinct()->pluck('wash')->map(fn ($wash): string => (string) $wash);

        $variants = ProductVariant::query()->whereIn('product_id', (clone $base)->select('id'));

        return [
            'cuts' => $termes->activeCuts($gender)
                ->filter(fn (Cut $cut): bool => $presentCuts->contains($cut->slug))
                ->map(fn (Cut $cut): array => ['value' => $cut->slug, 'label' => $cut->name])
                ->values()
                ->all(),
            'washes' => $termes->activeWashes()
                ->filter(fn (Wash $wash): bool => $presentWashes->contains($wash->slug))
                ->map(fn (Wash $wash): array => ['value' => $wash->slug, 'label' => $wash->name])
                ->values()
                ->all(),
            'sizes' => (clone $variants)->distinct()->orderBy('size')->pluck('size')->map(fn ($size): int => (int) $size)->values()->all(),
            'lengths' => (clone $variants)->distinct()->orderBy('length')->pluck('length')->map(fn ($length): int => (int) $length)->values()->all(),
            'sorts' => array_map(
                fn (string $sort): array => ['value' => $sort, 'label' => __("storefront.collections.sort.$sort")],
                self::$sorts,
            ),
        ];
    }
}
