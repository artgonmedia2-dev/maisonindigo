<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\Gender;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Storefront\Concerns\BrowsesCatalog;
use App\Http\Resources\ProductCardResource;
use App\Models\Article;
use App\Models\Collection;
use App\Models\CollectionWash;
use App\Models\Cut;
use App\Models\Product;
use App\Models\Wash;
use App\Services\SeoService;
use App\Support\CatalogTerms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Hubs de coupe et sous-collections lavage.
 *
 *   /homme/jean-baggy          le hub, une page par couple genre × coupe
 *   /homme/jean-baggy/noir     la sous-collection, quatre lavages au plus
 *
 * Le hub existe dès que la coupe est proposée pour ce genre. Une collection
 * peut lui être rattachée dans le back-office pour lui donner son intro, ses
 * sections et sa FAQ ; sans elle, la page reste servie, seulement plus nue.
 */
class CutHubController extends Controller
{
    use BrowsesCatalog;

    public const PER_PAGE = 12;

    public function show(Request $request, SeoService $seo, string $genre, string $coupe): Response
    {
        [$gender, $cut] = $this->resolve($genre, $coupe);

        $hub = $this->hub($gender, $cut);
        $filters = $this->catalogFilters($request);
        $products = $this->products($gender, $cut, $filters, $request);

        $breadcrumb = $this->breadcrumb($gender, $cut);

        return Inertia::render('Collection/Hub', [
            'seo' => $seo->forHub($cut, $gender->value, $hub, $products->all(), $breadcrumb, $this->isFaceted($filters)),
            'breadcrumb' => $breadcrumb,
            'heading' => __('seo.hub.heading', ['cut' => Str::lower($cut->name), 'gender' => $gender->value]),
            'intro' => $hub?->intro,
            'blocks' => $this->blocks($hub?->content_blocks),
            'faq' => $this->faq($hub?->faq),
            'filters' => $filters,
            'washPath' => null,
            ...$this->grid($gender, $cut, $filters, $request),
            'links' => $this->links($gender, $cut, $hub),
        ]);
    }

    public function wash(Request $request, SeoService $seo, string $genre, string $coupe, string $lavage): Response
    {
        [$gender, $cut] = $this->resolve($genre, $coupe);

        // Quatre lavages seulement méritent une adresse propre. Les autres
        // restent accessibles en filtre de la grille, pas en page indexable.
        $slug = CollectionWash::washFor($lavage);
        abort_if($slug === null, 404);

        $wash = app(CatalogTerms::class)->wash($slug);
        abort_if($wash === null || ! $wash->is_active, 404);

        $hub = $this->hub($gender, $cut);
        $page = $hub?->washPages->firstWhere('wash', $slug);
        abort_if($page !== null && ! $page->is_visible, 404);

        $filters = $this->catalogFilters($request);
        $filters['wash'] = [$wash->slug];

        $products = $this->products($gender, $cut, $filters, $request);
        $breadcrumb = $this->breadcrumb($gender, $cut, $wash);

        $remplacements = ['cut' => Str::lower($cut->name), 'gender' => $gender->value, 'wash' => Str::lower($wash->name)];

        return Inertia::render('Collection/Hub', [
            'seo' => $seo->forWash($cut, $wash, $gender->value, $page, $products->all(), $breadcrumb, $this->isFaceted($filters, washIsPath: true)),
            'breadcrumb' => $breadcrumb,
            'heading' => __('seo.wash.heading', $remplacements),
            'intro' => $page?->intro,
            'blocks' => [],
            'faq' => $this->faq($page?->faq),
            'filters' => $filters,
            'washPath' => $lavage,
            ...$this->grid($gender, $cut, $filters, $request),
            'links' => $this->links($gender, $cut, $hub),
        ]);
    }

    /**
     * @return array{0: Gender, 1: Cut}
     */
    private function resolve(string $genre, string $coupe): array
    {
        $gender = Gender::tryFrom($genre);
        abort_if($gender === null, 404);

        $cut = app(CatalogTerms::class)->cutByUrlSegment($coupe);
        abort_if($cut === null || ! $cut->is_active || ! $cut->isAvailableFor($gender), 404);

        return [$gender, $cut];
    }

    private function hub(Gender $gender, Cut $cut): ?Collection
    {
        return Collection::query()
            ->where('hub_gender', $gender)
            ->where('hub_cut', $cut->slug)
            ->with('washPages')
            ->first();
    }

    /**
     * @return Builder<Product>
     */
    private function baseQuery(Gender $gender, Cut $cut): Builder
    {
        return Product::query()->active()->where('gender', $gender)->where('cut', $cut->slug);
    }

    /**
     * @param  array{cut: list<string>, wash: list<string>, size: int|null, length: int|null, sort: string}  $filters
     * @return \Illuminate\Support\Collection<int, Product>
     */
    private function products(Gender $gender, Cut $cut, array $filters, Request $request): \Illuminate\Support\Collection
    {
        return $this->paginator($gender, $cut, $filters, $request)->getCollection();
    }

    /**
     * @param  array{cut: list<string>, wash: list<string>, size: int|null, length: int|null, sort: string}  $filters
     * @return LengthAwarePaginator<int, Product>
     */
    private function paginator(Gender $gender, Cut $cut, array $filters, Request $request): LengthAwarePaginator
    {
        $page = max(1, (int) $request->query('page', '1'));

        return $this->applyCatalogFilters($this->baseQuery($gender, $cut), $filters)
            ->with(['variants', 'media'])
            ->paginate(self::PER_PAGE, ['*'], 'page', $page)
            ->withQueryString();
    }

    /**
     * @param  array{cut: list<string>, wash: list<string>, size: int|null, length: int|null, sort: string}  $filters
     * @return array<string, mixed>
     */
    private function grid(Gender $gender, Cut $cut, array $filters, Request $request): array
    {
        $paginator = $this->paginator($gender, $cut, $filters, $request);

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
            'options' => $this->catalogFacets($this->baseQuery($gender, $cut), $gender),
        ];
    }

    /**
     * Fil d'Ariane : Accueil › Homme › Jean baggy › Noir.
     *
     * @return list<array{name: string, url: string}>
     */
    private function breadcrumb(Gender $gender, Cut $cut, ?Wash $wash = null): array
    {
        $base = rtrim((string) config('app.url'), '/');
        $genreUrl = $gender === Gender::Homme ? '/homme' : '/femme';
        $hubUrl = "/{$gender->value}/jean-{$cut->urlSegment()}";

        $trail = [
            ['name' => __('seo.breadcrumb.home'), 'url' => $base.'/'],
            ['name' => $gender->getLabel(), 'url' => $base.$genreUrl],
            ['name' => __('seo.hub.heading', ['cut' => Str::lower($cut->name), 'gender' => '']), 'url' => $base.$hubUrl],
        ];

        if ($wash !== null) {
            $trail[] = ['name' => $wash->name, 'url' => $base.$hubUrl.'/'.(CollectionWash::segmentFor($wash->slug) ?? $wash->slug)];
        }

        return array_map(fn (array $item): array => ['name' => trim($item['name']), 'url' => $item['url']], $trail);
    }

    /**
     * Maillage sortant : sous-collections lavage, collections sœurs, trois
     * articles du Journal, quiz taille. Aucune page orpheline.
     *
     * @return array<string, mixed>
     */
    private function links(Gender $gender, Cut $cut, ?Collection $hub): array
    {
        $termes = app(CatalogTerms::class);
        $hubPath = "/{$gender->value}/jean-{$cut->urlSegment()}";

        $washes = $termes->activeWashes()
            ->filter(fn (Wash $wash): bool => CollectionWash::segmentFor($wash->slug) !== null)
            ->filter(function (Wash $wash) use ($hub): bool {
                $page = $hub?->washPages->firstWhere('wash', $wash->slug);

                return $page === null || $page->is_visible;
            })
            ->map(fn (Wash $wash): array => [
                'label' => $wash->name,
                'url' => $hubPath.'/'.CollectionWash::segmentFor($wash->slug),
            ])
            ->values()
            ->all();

        $sisters = $termes->activeCuts($gender)
            ->reject(fn (Cut $autre): bool => $autre->slug === $cut->slug)
            ->take(3)
            ->map(fn (Cut $autre): array => [
                'label' => __('seo.hub.heading', ['cut' => Str::lower($autre->name), 'gender' => $gender->value]),
                'url' => "/{$gender->value}/jean-{$autre->urlSegment()}",
            ])
            ->values()
            ->all();

        $articles = Article::query()
            ->published()
            ->orderBy('position')
            ->limit(3)
            ->get(['slug', 'title', 'excerpt'])
            ->map(fn (Article $article): array => [
                'label' => $article->title,
                'url' => "/journal/{$article->slug}",
                'excerpt' => Str::limit($article->excerpt, 110),
            ])
            ->all();

        return [
            'washes' => $washes,
            'sisters' => $sisters,
            'articles' => $articles,
            'quiz' => '/trouver-ma-taille',
        ];
    }

    /**
     * @param  list<array{title?: string, body?: string}>|null  $blocks
     * @return list<array{title: string, body: string}>
     */
    private function blocks(?array $blocks): array
    {
        return array_values(array_map(
            fn (array $block): array => ['title' => (string) ($block['title'] ?? ''), 'body' => (string) ($block['body'] ?? '')],
            array_filter($blocks ?? [], fn ($block): bool => is_array($block) && filled($block['title'] ?? null)),
        ));
    }

    /**
     * @param  list<array{question?: string, answer?: string}>|null  $faq
     * @return list<array{question: string, answer: string}>
     */
    private function faq(?array $faq): array
    {
        return array_values(array_map(
            fn (array $entry): array => ['question' => (string) ($entry['question'] ?? ''), 'answer' => (string) ($entry['answer'] ?? '')],
            array_filter($faq ?? [], fn ($entry): bool => is_array($entry) && filled($entry['question'] ?? null) && filled($entry['answer'] ?? null)),
        ));
    }
}
