<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

/**
 * robots.txt.
 *
 * Les facettes en paramètres d'URL portent déjà « noindex, follow » : on ne
 * les bloque pas au crawl, sinon les robots ne verraient jamais la directive
 * ni la canonical. On écarte seulement ce qui n'a aucune raison d'être exploré.
 */
class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $base = rtrim((string) config('app.url'), '/');

        $lignes = [
            'User-agent: *',
            'Allow: /',
            '',
            'Disallow: /admin',
            'Disallow: /panier',
            'Disallow: /commande',
            'Disallow: /profile',
            'Disallow: /dashboard',
            'Disallow: /login',
            'Disallow: /register',
            '',
            "Sitemap: {$base}/sitemap.xml",
            '',
        ];

        // Un site hors production ne doit jamais se retrouver indexé.
        if (! app()->isProduction()) {
            $lignes = ['User-agent: *', 'Disallow: /', ''];
        }

        return response(implode("\n", $lignes), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
