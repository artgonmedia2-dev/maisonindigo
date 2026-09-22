<?php

use App\Enums\Gender;
use App\Models\Article;
use App\Models\Author;
use App\Models\Collection;
use App\Models\CollectionWash;
use Database\Seeders\DemoCatalogSeeder;
use Database\Seeders\SeoClusterSeeder;
use Database\Seeders\ShippingZonesSeeder;
use Database\Seeders\SizeChartsSeeder;

beforeEach(fn () => $this->seed([SizeChartsSeeder::class, ShippingZonesSeeder::class, DemoCatalogSeeder::class, SeoClusterSeeder::class]));

it('installe le hub baggy homme avec son contenu', function () {
    $hub = Collection::query()->where('hub_gender', Gender::Homme)->where('hub_cut', 'baggy')->firstOrFail();

    expect($hub->intro)->toContain('24 cm')
        ->and($hub->content_blocks)->toHaveCount(6)
        ->and($hub->faq)->toHaveCount(8)
        ->and($hub->meta_title)->toBe('Jean baggy homme premium — Maison Indigo Maroc')
        ->and(mb_strlen((string) $hub->meta_title))->toBeLessThanOrEqual(60)
        ->and(mb_strlen((string) $hub->meta_description))->toBeLessThanOrEqual(155);
});

it('installe les quatre sous-collections lavage', function () {
    $pages = CollectionWash::query()->get();

    expect($pages)->toHaveCount(4)
        ->and($pages->pluck('wash')->all())->toEqualCanonicalizing(array_values(CollectionWash::INDEXABLE));

    $pages->each(fn (CollectionWash $page) => expect($page->faq)->toHaveCount(3)->and($page->intro)->not->toBeEmpty());
});

it('installe les six articles avec leur réponse directe', function () {
    $articles = Article::query()->published()->get();

    expect($articles)->toHaveCount(6)
        ->and(Author::query()->count())->toBe(1);

    $articles->each(function (Article $article): void {
        expect($article->excerpt)->not->toBeEmpty()
            ->and($article->content_blocks)->not->toBeEmpty()
            ->and($article->faq)->toHaveCount(4)
            ->and($article->author_id)->not->toBeNull()
            // La voix de la maison : pas de point d'exclamation.
            ->and($article->excerpt)->not->toContain('!');
    });
});

it('se rejoue sans rien dupliquer', function () {
    $this->seed(SeoClusterSeeder::class);

    expect(Collection::query()->whereNotNull('hub_cut')->count())->toBe(1)
        ->and(CollectionWash::query()->count())->toBe(4)
        ->and(Article::query()->count())->toBe(6)
        ->and(Author::query()->count())->toBe(1);
});
