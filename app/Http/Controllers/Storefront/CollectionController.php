<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\Cut;
use App\Enums\Gender;
use App\Enums\Wash;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCardResource;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\CatalogCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Collections : Femme, Homme, Nouveautés, Atelier.
 * Filtres coupe / lavage / taille, tri, pagination. Résultats en cache, invalidés à la sauvegarde produit.
 */
class CollectionController extends Controller
{
    public const HANDLES = ['women', 'men', 'new', 'atelier'];

    public const PER_PAGE = 12;

    /** @var list<string> */
    public const SORTS = ['new', 'price_asc', 'price_desc'];

    public function show(Request $request, string $handle): Response
    {
        abort_unless(in_array($handle, self::HANDLES, true), 404);

        $filters = $this->filters($request);
        $page = max(1, (int) $request->query('page', '1'));

        $payload = Cache::remember(
            CatalogCache::key('collection', ['handle' => $handle, 'filters' => $filters, 'page' => $page]),
            now()->addMinutes(10),
            fn (): array => $this->build($request, $handle, $filters, $page),
        );

        return Inertia::render('Collection/Show', [
            'handle' => $handle,
            'meta' => [
                'title' => __("storefront.collections.$handle.title"),
                'description' => __("storefront.collections.$handle.description"),
            ],
            'heading' => __("storefront.collections.$handle.title"),
            'lead' => __("storefront.collections.$handle.lead"),
            'filters' => $filters,
            ...$payload,
        ]);
    }

    /**
     * @return array{cut: list<string>, wash: list<string>, size: int|null, sort: string}
     */
    private function filters(Request $request): array
    {
        $cuts = array_values(array_filter(
            (array) $request->query('cut', []),
            fn ($value): bool => is_string($value) && Cut::tryFrom($value) !== null,
        ));
        $washes = array_values(array_filter(
            (array) $request->query('wash', []),
            fn ($value): bool => is_string($value) && Wash::tryFrom($value) !== null,
        ));
        $size = (int) $request->query('size', '0');
        $sort = (string) $request->query('sort', 'new');

        return [
            'cut' => array_map('strval', $cuts),
            'wash' => array_map('strval', $washes),
            'size' => in_array($size, ProductVariant::SIZES, true) ? $size : null,
            'sort' => in_array($sort, self::SORTS, true) ? $sort : 'new',
        ];
    }

    /**
     * @param  array{cut: list<string>, wash: list<string>, size: int|null, sort: string}  $filters
     * @return array<string, mixed>
     */
    private function build(Request $request, string $handle, array $filters, int $page): array
    {
        $base = $this->baseQuery($handle);

        $query = (clone $base)
            ->when($filters['cut'] !== [], fn (Builder $query) => $query->whereIn('cut', $filters['cut']))
            ->when($filters['wash'] !== [], fn (Builder $query) => $query->whereIn('wash', $filters['wash']))
            ->when($filters['size'] !== null, fn (Builder $query) => $query->whereHas(
                'variants',
                fn (Builder $variants) => $variants->where('size', $filters['size'])->where('stock', '>', 0),
            ));

        match ($filters['sort']) {
            'price_asc' => $query->orderBy('price')->orderBy('id'),
            'price_desc' => $query->orderByDesc('price')->orderBy('id'),
            default => $query->orderByDesc('is_new')->orderByDesc('created_at')->orderBy('id'),
        };

        $paginator = $query
            ->with(['variants', 'media'])
            ->paginate(self::PER_PAGE, ['*'], 'page', $page)
            ->withQueryString();

        $gender = $this->gender($handle);
        $availableCuts = $gender === null ? Cut::cases() : Cut::forGender($gender);
        $presentWashes = (clone $base)->distinct()->pluck('wash')->map(fn ($wash): string => $wash instanceof Wash ? $wash->value : (string) $wash);
        $presentSizes = ProductVariant::query()
            ->whereIn('product_id', (clone $base)->select('id'))
            ->distinct()
            ->orderBy('size')
            ->pluck('size')
            ->map(fn ($size): int => (int) $size)
            ->values();

        return [
            'products' => ProductCardResource::collection($paginator->getCollection())->toArray($request),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'prev_url' => $paginator->previousPageUrl(),
                'next_url' => $paginator->nextPageUrl(),
            ],
            'options' => [
                'cuts' => array_map(fn (Cut $cut): array => ['value' => $cut->value, 'label' => $cut->getLabel()], $availableCuts),
                'washes' => collect(Wash::cases())
                    ->filter(fn (Wash $wash): bool => $presentWashes->contains($wash->value))
                    ->map(fn (Wash $wash): array => ['value' => $wash->value, 'label' => $wash->getLabel()])
                    ->values()
                    ->all(),
                'sizes' => $presentSizes->all(),
                'sorts' => array_map(fn (string $sort): array => ['value' => $sort, 'label' => __("storefront.collections.sort.$sort")], self::SORTS),
            ],
        ];
    }

    /**
     * @return Builder<Product>
     */
    private function baseQuery(string $handle): Builder
    {
        $query = Product::query()->active();

        return match ($handle) {
            'women' => $query->where('gender', Gender::Femme),
            'men' => $query->where('gender', Gender::Homme),
            'new' => $query->where('is_new', true),
            default => $query->where('is_atelier', true),
        };
    }

    private function gender(string $handle): ?Gender
    {
        return match ($handle) {
            'women' => Gender::Femme,
            'men' => Gender::Homme,
            default => null,
        };
    }
}
