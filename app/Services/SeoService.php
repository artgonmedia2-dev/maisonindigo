<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Author;
use App\Models\Collection;
use App\Models\CollectionWash;
use App\Models\Cut;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Wash;
use App\Settings\ShopSettings;
use App\Support\CatalogTerms;
use Illuminate\Support\Str;

/**
 * Title, meta, canonical, Open Graph et JSON-LD de chaque gabarit.
 *
 * Le rendu se fait côté serveur, dans le gabarit Blade : le SSR Inertia est
 * désactivé sur l'hébergement mutualisé, et un robot ou un moteur génératif
 * qui ne lit pas le JavaScript doit voir les schemas dans le HTML brut.
 *
 * Toutes les méthodes renvoient la même forme, que Blade sait rendre.
 *
 * @phpstan-type SeoPayload array{
 *     title: string,
 *     description: string,
 *     canonical: string,
 *     robots: string,
 *     og: array{type: string, title: string, description: string, url: string, image: string|null},
 *     schemas: list<array<string, mixed>>,
 *     breadcrumb: list<array{name: string, url: string}>
 * }
 */
class SeoService
{
    public function __construct(
        private readonly CatalogTerms $termes,
        private readonly ShopSettings $settings,
    ) {}

    /**
     * Hub de coupe : /homme/jean-baggy.
     *
     * @param  list<Product>  $products
     * @param  list<array{name: string, url: string}>  $breadcrumb
     * @return SeoPayload
     */
    public function forHub(Cut $cut, string $gender, ?Collection $hub, array $products, array $breadcrumb, bool $faceted): array
    {
        $canonical = $this->url("/{$gender}/jean-{$cut->urlSegment()}");
        $remplacements = ['cut' => Str::lower($cut->name), 'gender' => $gender];

        return $this->payload(
            title: $hub?->meta_title ?: __('seo.hub.title', $remplacements),
            description: $hub?->meta_description ?: __('seo.hub.description', $remplacements),
            canonical: $canonical,
            faceted: $faceted,
            breadcrumb: $breadcrumb,
            schemas: [
                $this->collectionPage(__('seo.hub.heading', $remplacements), $hub?->intro, $canonical),
                $this->itemList($products, $canonical),
                ...$this->faqSchema($hub?->faq),
                $this->breadcrumbList($breadcrumb),
            ],
            image: $this->firstImage($products),
        );
    }

    /**
     * Sous-collection lavage : /homme/jean-baggy/noir.
     *
     * @param  list<Product>  $products
     * @param  list<array{name: string, url: string}>  $breadcrumb
     * @return SeoPayload
     */
    public function forWash(Cut $cut, Wash $wash, string $gender, ?CollectionWash $page, array $products, array $breadcrumb, bool $faceted): array
    {
        $segment = CollectionWash::segmentFor($wash->slug) ?? $wash->slug;
        $canonical = $this->url("/{$gender}/jean-{$cut->urlSegment()}/{$segment}");
        $remplacements = ['cut' => Str::lower($cut->name), 'gender' => $gender, 'wash' => Str::lower($wash->name)];

        return $this->payload(
            title: $page?->meta_title ?: __('seo.wash.title', $remplacements),
            description: $page?->meta_description ?: __('seo.wash.description', $remplacements),
            canonical: $canonical,
            faceted: $faceted,
            breadcrumb: $breadcrumb,
            schemas: [
                $this->collectionPage(__('seo.wash.heading', $remplacements), $page?->intro, $canonical),
                $this->itemList($products, $canonical),
                ...$this->faqSchema($page?->faq),
                $this->breadcrumbList($breadcrumb),
            ],
            image: $this->firstImage($products),
        );
    }

    /**
     * Fiche produit : /homme/baggy-indigo-brut.
     *
     * @param  list<array{name: string, url: string}>  $breadcrumb
     * @return SeoPayload
     */
    public function forProduct(Product $product, array $breadcrumb): array
    {
        $canonical = $this->url($this->productPath($product));

        return $this->payload(
            title: $product->meta_title ?: __('seo.product.title', $this->productReplacements($product)),
            description: $product->meta_description ?: Str::limit((string) $product->description, 155, ''),
            canonical: $canonical,
            faceted: false,
            breadcrumb: $breadcrumb,
            schemas: [
                $this->productSchema($product, $canonical),
                $this->breadcrumbList($breadcrumb),
            ],
            image: $product->getFirstMediaUrl(Product::MEDIA_GALLERY) ?: null,
            type: 'product',
        );
    }

    /**
     * Article du Journal.
     *
     * @param  list<array{name: string, url: string}>  $breadcrumb
     * @return SeoPayload
     */
    public function forArticle(Article $article, array $breadcrumb): array
    {
        $canonical = $this->url("/journal/{$article->slug}");

        return $this->payload(
            title: $article->meta_title ?: __('seo.article.title', ['title' => $article->title]),
            description: $article->meta_description ?: Str::limit((string) $article->excerpt, 155, ''),
            canonical: $canonical,
            faceted: false,
            breadcrumb: $breadcrumb,
            schemas: [
                $this->articleSchema($article, $canonical),
                ...$this->faqSchema($article->faq),
                $this->breadcrumbList($breadcrumb),
            ],
            image: $article->getFirstMediaUrl(Article::MEDIA_COVER) ?: null,
            type: 'article',
        );
    }

    /**
     * @param  list<array{name: string, url: string}>  $breadcrumb
     * @return SeoPayload
     */
    public function forAuthor(Author $author, array $breadcrumb): array
    {
        $canonical = $this->url("/journal/auteur/{$author->slug}");

        return $this->payload(
            title: __('seo.author.title', ['name' => $author->name]),
            description: __('seo.author.description', ['name' => $author->name]),
            canonical: $canonical,
            faceted: false,
            breadcrumb: $breadcrumb,
            schemas: [$this->breadcrumbList($breadcrumb)],
        );
    }

    /**
     * Gabarit générique : accueil, pages d'information, index du Journal.
     *
     * @param  list<array{name: string, url: string}>  $breadcrumb
     * @return SeoPayload
     */
    public function forPage(string $title, string $description, string $path, array $breadcrumb = [], bool $faceted = false): array
    {
        return $this->payload(
            title: $title,
            description: $description,
            canonical: $this->url($path),
            faceted: $faceted,
            breadcrumb: $breadcrumb,
            schemas: $breadcrumb === [] ? [] : [$this->breadcrumbList($breadcrumb)],
        );
    }

    /**
     * L'entité de marque, rendue sur chaque page. C'est la description que
     * les moteurs génératifs reprennent : elle ne varie jamais.
     *
     * @return array<string, mixed>
     */
    public function organization(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'ClothingStore',
            '@id' => $this->url('/#organization'),
            'name' => __('seo.brand'),
            'description' => __('seo.entity'),
            'url' => $this->url('/'),
            'email' => $this->settings->contact_email,
            'telephone' => $this->settings->contact_whatsapp,
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => __('seo.city'),
                'addressCountry' => 'MA',
            ],
            'areaServed' => ['@type' => 'Country', 'name' => __('seo.country')],
            'priceRange' => '399–599 MAD',
            'currenciesAccepted' => 'MAD',
            'paymentAccepted' => 'Paiement à la livraison, virement bancaire',
        ];
    }

    /** L'adresse publique d'un produit : /{genre}/{slug}. */
    public function productPath(Product $product): string
    {
        return $product->path();
    }

    /**
     * @param  list<array{name: string, url: string}>  $breadcrumb
     * @param  list<array<string, mixed>>  $schemas
     * @return SeoPayload
     */
    private function payload(
        string $title,
        string $description,
        string $canonical,
        bool $faceted,
        array $breadcrumb,
        array $schemas,
        ?string $image = null,
        string $type = 'website',
    ): array {
        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            // Une facette n'est jamais indexée : elle renvoie vers l'adresse
            // propre, qui porte seule le référencement de la page.
            'robots' => __($faceted ? 'seo.robots.noindex' : 'seo.robots.index'),
            'og' => [
                'type' => $type,
                'title' => $title,
                'description' => $description,
                'url' => $canonical,
                'image' => $image,
            ],
            'schemas' => array_values(array_filter($schemas)),
            'breadcrumb' => $breadcrumb,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function collectionPage(string $name, ?string $intro, string $url): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $name,
            'description' => $intro === null ? null : Str::limit(strip_tags($intro), 300, ''),
            'url' => $url,
            'isPartOf' => ['@id' => $this->url('/#organization')],
        ]);
    }

    /**
     * @param  list<Product>  $products
     * @return array<string, mixed>|null
     */
    private function itemList(array $products, string $url): ?array
    {
        if ($products === []) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'url' => $url,
            'numberOfItems' => count($products),
            'itemListElement' => array_values(array_map(fn (int $index, Product $product): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'url' => $this->url($this->productPath($product)),
                'name' => $product->title,
            ], array_keys($products), $products)),
        ];
    }

    /**
     * Product complet : offers par variante en MAD, marque, matière, taille,
     * couleur. Ce sont ces champs que les résultats enrichis exigent.
     *
     * @return array<string, mixed>
     */
    private function productSchema(Product $product, string $url): array
    {
        $product->loadMissing(['variants', 'media']);

        $offers = $product->variants
            ->map(fn (ProductVariant $variant): array => [
                '@type' => 'Offer',
                'sku' => $variant->sku,
                'url' => $url,
                'priceCurrency' => 'MAD',
                'price' => number_format($product->price / 100, 2, '.', ''),
                'availability' => $variant->stock > 0
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'itemCondition' => 'https://schema.org/NewCondition',
                'seller' => ['@id' => $this->url('/#organization')],
            ])
            ->values()
            ->all();

        $images = $product->getMedia(Product::MEDIA_GALLERY)
            ->map(fn ($media): string => $media->getUrl())
            ->values()
            ->all();

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->title,
            'description' => $product->description,
            'sku' => $product->skuPrefix(),
            'url' => $url,
            'image' => $images === [] ? null : $images,
            'brand' => ['@type' => 'Brand', 'name' => __('seo.brand')],
            'material' => $product->composition,
            'color' => $product->washLabel(),
            'size' => $this->sizeRange($product),
            'audience' => ['@type' => 'PeopleAudience', 'suggestedGender' => $product->gender->value],
            'offers' => $offers === [] ? null : $offers,
        ], fn ($value): bool => $value !== null && $value !== []);
    }

    /**
     * @param  list<array{question: string, answer: string}>|null  $faq
     * @return list<array<string, mixed>>
     */
    private function faqSchema(?array $faq): array
    {
        $entries = array_values(array_filter(
            $faq ?? [],
            fn ($entry): bool => is_array($entry) && filled($entry['question'] ?? null) && filled($entry['answer'] ?? null),
        ));

        if ($entries === []) {
            return [];
        }

        return [[
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn (array $entry): array => [
                '@type' => 'Question',
                'name' => $entry['question'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $entry['answer']],
            ], $entries),
        ]];
    }

    /**
     * @return array<string, mixed>
     */
    private function articleSchema(Article $article, string $url): array
    {
        $article->loadMissing('author');

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article->title,
            'description' => $article->excerpt,
            'url' => $url,
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $url],
            'datePublished' => $article->published_at?->toAtomString(),
            'dateModified' => $article->updated_at?->toAtomString(),
            'author' => $article->author === null ? null : [
                '@type' => 'Person',
                'name' => $article->author->name,
                'url' => $this->url("/journal/auteur/{$article->author->slug}"),
            ],
            'publisher' => ['@id' => $this->url('/#organization')],
            'image' => $article->getFirstMediaUrl(Article::MEDIA_COVER) ?: null,
        ]);
    }

    /**
     * @param  list<array{name: string, url: string}>  $breadcrumb
     * @return array<string, mixed>|null
     */
    private function breadcrumbList(array $breadcrumb): ?array
    {
        if ($breadcrumb === []) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_values(array_map(fn (int $index, array $item): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ], array_keys($breadcrumb), $breadcrumb)),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function productReplacements(Product $product): array
    {
        return [
            'title' => $product->title,
            'gender' => $product->gender->value,
            'cut' => Str::lower($product->cutLabel()),
            'origin' => Str::lower((string) $product->fabric_origin),
            'weight' => rtrim(rtrim(number_format((float) $product->weight_oz, 1, ',', ''), '0'), ','),
            'sizes' => $this->sizeRange($product),
        ];
    }

    private function sizeRange(Product $product): string
    {
        $product->loadMissing('variants');

        $sizes = $product->variants->pluck('size')->map(fn ($size): int => (int) $size);

        return $sizes->isEmpty() ? '28–42' : $sizes->min().'–'.$sizes->max();
    }

    /**
     * @param  list<Product>  $products
     */
    private function firstImage(array $products): ?string
    {
        foreach ($products as $product) {
            $url = $product->getFirstMediaUrl(Product::MEDIA_GALLERY);

            if ($url !== '') {
                return $url;
            }
        }

        return null;
    }

    private function url(string $path): string
    {
        return rtrim((string) config('app.url'), '/').'/'.ltrim($path, '/');
    }

    /** Le vocabulaire reste accessible aux appelants qui composent un fil. */
    public function terms(): CatalogTerms
    {
        return $this->termes;
    }
}
