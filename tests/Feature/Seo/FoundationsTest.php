<?php

use App\Models\Article;
use App\Models\Product;
use Database\Seeders\DemoCatalogSeeder;
use Database\Seeders\SeoClusterSeeder;
use Database\Seeders\ShippingZonesSeeder;
use Database\Seeders\SizeChartsSeeder;

beforeEach(fn () => $this->seed([SizeChartsSeeder::class, ShippingZonesSeeder::class, DemoCatalogSeeder::class, SeoClusterSeeder::class]));

it('publie un plan du site complet et valide', function () {
    $response = $this->get('/sitemap.xml')->assertOk();

    $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

    $xml = simplexml_load_string($response->getContent());
    expect($xml)->not->toBeFalse();

    $urls = array_map(fn ($url): string => (string) $url->loc, iterator_to_array($xml->url, false));
    $base = rtrim((string) config('app.url'), '/');

    expect($urls)
        ->toContain($base.'/')
        ->toContain($base.'/homme')
        ->toContain($base.'/journal')
        ->toContain($base.'/la-maison')
        // Les hubs de coupe et leurs sous-collections lavage.
        ->toContain($base.'/homme/jean-baggy')
        ->toContain($base.'/homme/jean-baggy/noir')
        ->toContain($base.'/homme/jean-baggy/bleu')
        ->toContain($base.'/homme/jean-baggy/brut')
        ->toContain($base.'/homme/jean-baggy/clair')
        ->toContain($base.'/femme/jean-baggy');

    $product = Product::query()->active()->firstOrFail();
    $article = Article::query()->published()->firstOrFail();

    expect($urls)
        ->toContain($base.$product->path())
        ->toContain($base."/journal/{$article->slug}")
        ->toContain($base.'/journal/auteur/atelier-maison-indigo');
});

it('ne se déclare pas non indexable', function () {
    $response = $this->get('/sitemap.xml')->assertOk();

    // Google refuse de lire un plan du site marqué noindex et répond
    // « Impossible de lire le sitemap » sans dire pourquoi.
    expect($response->headers->get('X-Robots-Tag'))->toBeNull();
});

it('ne laisse aucun fichier statique masquer les routes des robots', function (string $fichier) {
    // Un fichier dans public/ est servi par le serveur web avant d'atteindre
    // Laravel : la route ne s'exécuterait jamais en production, alors que les
    // tests, eux, passeraient.
    expect(file_exists(public_path($fichier)))->toBeFalse("public/{$fichier} masquerait la route du même nom.");
})->with(['robots.txt', 'sitemap.xml', 'llms.txt']);

it('laisse les facettes hors du plan du site', function () {
    $contenu = $this->get('/sitemap.xml')->getContent();

    expect($contenu)->not->toContain('?size=')
        ->and($contenu)->not->toContain('?sort=')
        ->and($contenu)->not->toContain('/homme/jean-baggy/stone');
});

it('se régénère quand un article paraît', function () {
    $avant = $this->get('/sitemap.xml')->getContent();

    $article = Article::factory()->create(['slug' => 'nouveau-guide', 'published_at' => now()->subHour()]);

    expect($this->get('/sitemap.xml')->getContent())
        ->not->toBe($avant)
        ->toContain("/journal/{$article->slug}");
});

it('publie llms.txt avec l’entité, les chiffres et les liens', function () {
    $response = $this->get('/llms.txt')->assertOk();

    $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

    $base = rtrim((string) config('app.url'), '/');

    expect($response->getContent())
        ->toContain('Maison Indigo, marque marocaine de jeans premium basée à Nador')
        ->toContain('12 à 14 oz')
        ->toContain('28 à 42')
        ->toContain('24 cm en baggy')
        ->toContain($base.'/homme/jean-baggy')
        ->toContain($base.'/journal/comment-porter-jean-baggy-homme')
        ->toContain($base.'/la-maison')
        ->toContain($base.'/trouver-ma-taille');
});

it('publie robots.txt et y annonce le plan du site', function () {
    // En production seulement : ailleurs, le site est fermé aux robots.
    app()->detectEnvironment(fn (): string => 'production');

    $contenu = $this->get('/robots.txt')->assertOk()->getContent();

    expect($contenu)
        ->toContain('Disallow: /admin')
        ->toContain('Disallow: /commande')
        ->toContain('Sitemap: '.rtrim((string) config('app.url'), '/').'/sitemap.xml');
});

it('interdit tout le site hors production', function () {
    expect(app()->isProduction())->toBeFalse()
        ->and($this->get('/robots.txt')->getContent())->toContain("User-agent: *\nDisallow: /");
});
