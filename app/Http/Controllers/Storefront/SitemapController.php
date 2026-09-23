<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\Gender;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Author;
use App\Models\Collection;
use App\Models\CollectionWash;
use App\Models\Cut;
use App\Models\Product;
use App\Support\CatalogCache;
use App\Support\CatalogTerms;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Le plan du site : collections, hubs de coupe, sous-collections lavage,
 * produits, articles, auteurs et pages d'information.
 *
 * Seules les adresses indexables y figurent. Les facettes en paramètres
 * d'URL n'y sont pas : elles portent déjà « noindex, follow ».
 *
 * Le rendu est mis en cache et invalidé par CatalogCache, donc à chaque
 * sauvegarde produit ou collection.
 */
class SitemapController extends Controller
{
    public function __invoke(CatalogTerms $termes): Response
    {
        $xml = Cache::remember(
            CatalogCache::key('sitemap'),
            now()->addHours(6),
            fn (): string => $this->build($termes),
        );

        // Surtout pas de X-Robots-Tag noindex ici : Google refuse de lire un
        // plan du site qui se déclare non indexable, et Search Console répond
        // « Impossible de lire le sitemap » sans dire pourquoi.
        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function build(CatalogTerms $termes): string
    {
        $base = rtrim((string) config('app.url'), '/');
        $now = Carbon::now()->toAtomString();

        /** @var list<array{loc: string, lastmod: string, changefreq: string, priority: string, images?: list<array{loc: string, title: string}>}> $urls */
        $urls = [];

        foreach ([
            ['/', 'daily', '1.0'],
            ['/homme', 'weekly', '0.9'],
            ['/femme', 'weekly', '0.9'],
            ['/nouveautes', 'daily', '0.8'],
            ['/atelier', 'weekly', '0.7'],
            ['/journal', 'weekly', '0.8'],
            ['/trouver-ma-taille', 'monthly', '0.7'],
            ['/guide-des-tailles', 'monthly', '0.6'],
            ['/la-maison', 'monthly', '0.6'],
            ['/entretien', 'monthly', '0.5'],
            ['/faq', 'monthly', '0.5'],
            ['/contact', 'monthly', '0.4'],
        ] as [$path, $freq, $priority]) {
            $urls[] = ['loc' => $base.$path, 'lastmod' => $now, 'changefreq' => $freq, 'priority' => $priority];
        }

        // Hubs de coupe et sous-collections lavage.
        $hubs = Collection::query()->whereNotNull('hub_cut')->with('washPages')->get()->keyBy(
            fn (Collection $hub): string => $hub->hub_gender?->value.'|'.$hub->hub_cut,
        );

        foreach ([Gender::Homme, Gender::Femme] as $gender) {
            foreach ($termes->activeCuts($gender) as $cut) {
                /** @var Cut $cut */
                $hubPath = "/{$gender->value}/jean-{$cut->urlSegment()}";
                $hub = $hubs->get($gender->value.'|'.$cut->slug);

                $urls[] = [
                    'loc' => $base.$hubPath,
                    'lastmod' => $hub?->updated_at?->toAtomString() ?? $now,
                    'changefreq' => 'weekly',
                    'priority' => '0.9',
                ];

                foreach (CollectionWash::INDEXABLE as $segment => $slug) {
                    $wash = $termes->wash($slug);
                    $page = $hub?->washPages->firstWhere('wash', $slug);

                    if ($wash === null || ! $wash->is_active || ($page !== null && ! $page->is_visible)) {
                        continue;
                    }

                    $urls[] = [
                        'loc' => $base.$hubPath.'/'.$segment,
                        'lastmod' => $page?->updated_at?->toAtomString() ?? $now,
                        'changefreq' => 'weekly',
                        'priority' => '0.7',
                    ];
                }
            }
        }

        foreach (Product::query()->active()->with('media')->get() as $product) {
            $urls[] = [
                'loc' => $base.$product->path(),
                'lastmod' => $product->updated_at?->toAtomString() ?? $now,
                'changefreq' => 'weekly',
                'priority' => '0.9',
                'images' => $product->getMedia(Product::MEDIA_GALLERY)
                    ->map(fn ($media): array => ['loc' => $media->getUrl(), 'title' => $product->title])
                    ->values()
                    ->all(),
            ];
        }

        foreach (Article::query()->published()->get() as $article) {
            $urls[] = [
                'loc' => $base."/journal/{$article->slug}",
                'lastmod' => $article->updated_at?->toAtomString() ?? $now,
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ];
        }

        foreach (Author::query()->has('articles')->get() as $author) {
            $urls[] = [
                'loc' => $base."/journal/auteur/{$author->slug}",
                'lastmod' => $author->updated_at?->toAtomString() ?? $now,
                'changefreq' => 'monthly',
                'priority' => '0.4',
            ];
        }

        return $this->render($urls);
    }

    /**
     * @param  list<array{loc: string, lastmod: string, changefreq: string, priority: string, images?: list<array{loc: string, title: string}>}>  $urls
     */
    private function render(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'."\n";
        $xml .= '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"'."\n";
        $xml .= '        xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

        foreach ($urls as $url) {
            $loc = htmlspecialchars($url['loc'], ENT_XML1);

            $xml .= "    <url>\n";
            $xml .= '        <loc>'.$loc."</loc>\n";
            $xml .= '        <lastmod>'.$url['lastmod']."</lastmod>\n";
            $xml .= '        <changefreq>'.$url['changefreq']."</changefreq>\n";
            $xml .= '        <priority>'.$url['priority']."</priority>\n";
            $xml .= '        <xhtml:link rel="alternate" hreflang="fr-MA" href="'.$loc."\" />\n";

            foreach ($url['images'] ?? [] as $image) {
                $xml .= "        <image:image>\n";
                $xml .= '            <image:loc>'.htmlspecialchars($image['loc'], ENT_XML1)."</image:loc>\n";
                $xml .= '            <image:title>'.htmlspecialchars($image['title'], ENT_XML1)."</image:title>\n";
                $xml .= "        </image:image>\n";
            }

            $xml .= "    </url>\n";
        }

        return $xml.'</urlset>';
    }
}
