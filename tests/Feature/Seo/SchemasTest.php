<?php

use App\Enums\Gender;
use App\Models\Article;
use App\Models\Product;
use Database\Seeders\DemoCatalogSeeder;
use Database\Seeders\SeoClusterSeeder;
use Database\Seeders\ShippingZonesSeeder;
use Database\Seeders\SizeChartsSeeder;

beforeEach(fn () => $this->seed([SizeChartsSeeder::class, ShippingZonesSeeder::class, DemoCatalogSeeder::class, SeoClusterSeeder::class]));

/**
 * Les schemas sont rendus côté serveur, dans le HTML brut : on les relit
 * exactement comme le ferait un robot ou un moteur génératif.
 *
 * @return list<array<string, mixed>>
 */
function schemas(string $html): array
{
    preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);

    return array_map(function (string $json): array {
        $decoded = json_decode(html_entity_decode($json, ENT_QUOTES | ENT_HTML5), true);

        expect($decoded)->toBeArray("Le JSON-LD doit être valide : {$json}");

        return $decoded;
    }, $matches[1]);
}

/**
 * @param  list<array<string, mixed>>  $schemas
 */
function schemaOfType(array $schemas, string $type): ?array
{
    foreach ($schemas as $schema) {
        if (($schema['@type'] ?? null) === $type) {
            return $schema;
        }
    }

    return null;
}

it('rend l’entité de marque sur chaque page', function (string $url) {
    $organization = schemaOfType(schemas($this->get($url)->getContent()), 'ClothingStore');

    expect($organization)->not->toBeNull()
        ->and($organization['description'])->toBe('Maison Indigo, marque marocaine de jeans premium basée à Nador')
        ->and($organization['address']['addressLocality'])->toBe('Nador')
        ->and($organization['address']['addressCountry'])->toBe('MA');
})->with(['/', '/homme/jean-baggy', '/journal', '/la-maison']);

it('rend CollectionPage, ItemList, FAQPage et BreadcrumbList sur le hub', function () {
    $schemas = schemas($this->get('/homme/jean-baggy')->getContent());

    $collection = schemaOfType($schemas, 'CollectionPage');
    $list = schemaOfType($schemas, 'ItemList');
    $faq = schemaOfType($schemas, 'FAQPage');
    $fil = schemaOfType($schemas, 'BreadcrumbList');

    expect($collection)->not->toBeNull()
        ->and($collection['name'])->toBe('Jean baggy homme')
        ->and($list)->not->toBeNull()
        ->and($list['numberOfItems'])->toBeGreaterThan(0)
        ->and($list['itemListElement'][0]['@type'])->toBe('ListItem')
        ->and($faq)->not->toBeNull()
        ->and($faq['mainEntity'])->toHaveCount(8)
        ->and($faq['mainEntity'][0]['acceptedAnswer']['@type'])->toBe('Answer')
        ->and($fil)->not->toBeNull()
        ->and($fil['itemListElement'])->toHaveCount(3);
});

it('rend la FAQ propre au lavage sur la sous-collection', function () {
    $schemas = schemas($this->get('/homme/jean-baggy/noir')->getContent());

    expect(schemaOfType($schemas, 'FAQPage')['mainEntity'])->toHaveCount(3)
        ->and(schemaOfType($schemas, 'BreadcrumbList')['itemListElement'])->toHaveCount(4);
});

it('rend un Product complet avec ses offres en dirhams', function () {
    $product = Product::query()->where('cut', 'baggy')->where('gender', Gender::Homme)->firstOrFail();

    $schema = schemaOfType(schemas($this->get($product->path())->getContent()), 'Product');

    expect($schema)->not->toBeNull()
        ->and($schema['name'])->toBe($product->title)
        ->and($schema['brand']['name'])->toBe('Maison Indigo')
        ->and($schema['material'])->not->toBeEmpty()
        ->and($schema['color'])->not->toBeEmpty()
        ->and($schema['size'])->toMatch('/^\d+–\d+$/')
        ->and($schema['offers'])->not->toBeEmpty()
        ->and($schema['offers'][0]['priceCurrency'])->toBe('MAD')
        ->and($schema['offers'][0]['availability'])->toStartWith('https://schema.org/')
        ->and($schema['offers'][0]['sku'])->toStartWith('MI-H-BAG-');
});

it('rend Article et FAQPage sur une page du Journal', function () {
    $article = Article::query()->published()->firstOrFail();

    $schemas = schemas($this->get("/journal/{$article->slug}")->getContent());
    $schema = schemaOfType($schemas, 'Article');

    expect($schema)->not->toBeNull()
        ->and($schema['headline'])->toBe($article->title)
        ->and($schema['description'])->toBe($article->excerpt)
        ->and($schema['author']['@type'])->toBe('Person')
        ->and($schema['datePublished'])->not->toBeEmpty()
        ->and(schemaOfType($schemas, 'FAQPage')['mainEntity'])->toHaveCount(4);
});

it('annonce la canonical et l’indexation sur une adresse propre', function (string $url) {
    $html = $this->get($url)->getContent();

    expect($html)->toContain('rel="canonical"')
        ->and($html)->toContain('name="robots" content="index, follow')
        ->and($html)->not->toContain('noindex');
})->with(['/homme/jean-baggy', '/homme/jean-baggy/noir', '/journal']);

it('désindexe les facettes et renvoie vers l’adresse propre', function (string $query) {
    $html = $this->get("/homme/jean-baggy?{$query}")->getContent();

    expect($html)->toContain('name="robots" content="noindex, follow"')
        // La canonical reste l'adresse sans paramètre : c'est elle qui porte
        // le référencement de la page.
        ->and($html)->toContain('<link rel="canonical" href="'.config('app.url').'/homme/jean-baggy">');
})->with(['size=32', 'length=32', 'sort=price_asc', 'wash[]=noir', 'cut[]=baggy']);

it('garde la sous-collection indexable malgré son lavage en chemin', function () {
    $html = $this->get('/homme/jean-baggy/noir')->getContent();

    expect($html)->toContain('name="robots" content="index, follow')
        ->and($html)->toContain('<link rel="canonical" href="'.config('app.url').'/homme/jean-baggy/noir">');
});

it('rend les Open Graph de chaque gabarit', function () {
    $html = $this->get('/homme/jean-baggy')->getContent();

    expect($html)->toContain('property="og:title"')
        ->and($html)->toContain('property="og:description"')
        ->and($html)->toContain('property="og:url"')
        ->and($html)->toContain('property="og:site_name" content="Maison Indigo"')
        ->and($html)->toContain('property="og:locale" content="fr_MA"');
});
