<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $baseUrl = config('app.url', 'https://royalblue-hedgehog-165827.hostingersite.com');
        $baseUrl = rtrim($baseUrl, '/');

        $now = Carbon::now()->toAtomString();

        $staticUrls = [
            [
                'loc' => $baseUrl,
                'lastmod' => $now,
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => $baseUrl.'/femme',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
            [
                'loc' => $baseUrl.'/homme',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
            [
                'loc' => $baseUrl.'/nouveautes',
                'lastmod' => $now,
                'changefreq' => 'daily',
                'priority' => '0.8',
            ],
            [
                'loc' => $baseUrl.'/atelier',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
            [
                'loc' => $baseUrl.'/trouver-ma-taille',
                'lastmod' => $now,
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
        ];

        $products = Product::active()
            ->with('media')
            ->select(['id', 'slug', 'title', 'meta_description', 'updated_at'])
            ->get();

        $productUrls = [];
        foreach ($products as $product) {
            $item = [
                'loc' => $baseUrl.'/produit/'.$product->slug,
                'lastmod' => $product->updated_at ? $product->updated_at->toAtomString() : $now,
                'changefreq' => 'weekly',
                'priority' => '0.9',
                'title' => $product->title,
                'images' => [],
            ];

            foreach ($product->getMedia(Product::MEDIA_GALLERY) as $media) {
                $item['images'][] = [
                    'loc' => $media->getUrl(),
                    'title' => $product->title,
                ];
            }

            $productUrls[] = $item;
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'."\n";
        $xml .= '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"'."\n";
        $xml .= '        xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

        foreach ($staticUrls as $url) {
            $xml .= "    <url>\n";
            $xml .= '        <loc>'.htmlspecialchars($url['loc'])."</loc>\n";
            $xml .= '        <lastmod>'.$url['lastmod']."</lastmod>\n";
            $xml .= '        <changefreq>'.$url['changefreq']."</changefreq>\n";
            $xml .= '        <priority>'.$url['priority']."</priority>\n";
            $xml .= '        <xhtml:link rel="alternate" hreflang="fr-MA" href="'.htmlspecialchars($url['loc'])."\" />\n";
            $xml .= '        <xhtml:link rel="alternate" hreflang="fr" href="'.htmlspecialchars($url['loc'])."\" />\n";
            $xml .= "    </url>\n";
        }

        foreach ($productUrls as $url) {
            $xml .= "    <url>\n";
            $xml .= '        <loc>'.htmlspecialchars($url['loc'])."</loc>\n";
            $xml .= '        <lastmod>'.$url['lastmod']."</lastmod>\n";
            $xml .= '        <changefreq>'.$url['changefreq']."</changefreq>\n";
            $xml .= '        <priority>'.$url['priority']."</priority>\n";
            $xml .= '        <xhtml:link rel="alternate" hreflang="fr-MA" href="'.htmlspecialchars($url['loc'])."\" />\n";
            $xml .= '        <xhtml:link rel="alternate" hreflang="fr" href="'.htmlspecialchars($url['loc'])."\" />\n";

            foreach ($url['images'] as $img) {
                $xml .= "        <image:image>\n";
                $xml .= '            <image:loc>'.htmlspecialchars($img['loc'])."</image:loc>\n";
                $xml .= '            <image:title>'.htmlspecialchars($img['title'])."</image:title>\n";
                $xml .= "        </image:image>\n";
            }

            $xml .= "    </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'text/xml; charset=UTF-8',
            'X-Robots-Tag' => 'noindex, follow',
        ]);
    }
}
