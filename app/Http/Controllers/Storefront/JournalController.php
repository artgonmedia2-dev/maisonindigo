<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCardResource;
use App\Models\Article;
use App\Models\Author;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Le Journal : index, article, page auteur.
 *
 * Un article non publié reste invisible du storefront, y compris par son
 * adresse directe : les brouillons se relisent dans le back-office.
 */
class JournalController extends Controller
{
    public function index(Request $request, SeoService $seo): Response
    {
        $articles = Article::query()
            ->published()
            ->with('author')
            ->orderBy('position')
            ->orderByDesc('published_at')
            ->get();

        $breadcrumb = $this->breadcrumb();

        return Inertia::render('Journal/Index', [
            'seo' => $seo->forPage(__('seo.journal.title'), __('seo.journal.description'), '/journal', $breadcrumb),
            'breadcrumb' => $breadcrumb,
            'articles' => $articles->map(fn (Article $article): array => $this->card($article))->all(),
        ]);
    }

    public function show(Request $request, SeoService $seo, string $slug): Response
    {
        $article = Article::query()
            ->published()
            ->where('slug', $slug)
            ->with(['author', 'products.variants', 'products.media'])
            ->firstOrFail();

        $breadcrumb = [...$this->breadcrumb(), [
            'name' => $article->title,
            'url' => $this->url("/journal/{$article->slug}"),
        ]];

        return Inertia::render('Journal/Show', [
            'seo' => $seo->forArticle($article, $breadcrumb),
            'breadcrumb' => $breadcrumb,
            'article' => [
                'slug' => $article->slug,
                'title' => $article->title,
                // « En bref » : la réponse directe, avant tout récit.
                'excerpt' => $article->excerpt,
                'blocks' => $this->blocks($article->content_blocks),
                'faq' => $this->faq($article->faq),
                'published_at' => $article->published_at?->toDateString(),
                'updated_at' => $article->updated_at?->toDateString(),
                'cover' => $article->getFirstMediaUrl(Article::MEDIA_COVER) ?: null,
                'author' => $article->author === null ? null : [
                    'name' => $article->author->name,
                    'role' => $article->author->role,
                    'url' => "/journal/auteur/{$article->author->slug}",
                ],
            ],
            'products' => ProductCardResource::collection($article->products)->toArray($request),
        ]);
    }

    public function author(Request $request, SeoService $seo, string $slug): Response
    {
        $author = Author::query()->where('slug', $slug)->firstOrFail();

        $articles = $author->articles()->published()->orderByDesc('published_at')->get();

        $breadcrumb = [...$this->breadcrumb(), [
            'name' => $author->name,
            'url' => $this->url("/journal/auteur/{$author->slug}"),
        ]];

        return Inertia::render('Journal/Author', [
            'seo' => $seo->forAuthor($author, $breadcrumb),
            'breadcrumb' => $breadcrumb,
            'author' => [
                'name' => $author->name,
                'role' => $author->role,
                'bio' => $author->bio,
                'portrait' => $author->getFirstMediaUrl(Author::MEDIA_PORTRAIT) ?: null,
            ],
            'articles' => $articles->map(fn (Article $article): array => $this->card($article))->all(),
        ]);
    }

    /**
     * @return array{slug: string, title: string, excerpt: string, published_at: string|null, author: string|null, cover: string|null}
     */
    private function card(Article $article): array
    {
        return [
            'slug' => $article->slug,
            'title' => $article->title,
            'excerpt' => $article->excerpt,
            'published_at' => $article->published_at?->toDateString(),
            'author' => $article->author?->name,
            'cover' => $article->getFirstMediaUrl(Article::MEDIA_COVER) ?: null,
        ];
    }

    /**
     * @return list<array{name: string, url: string}>
     */
    private function breadcrumb(): array
    {
        return [
            ['name' => __('seo.breadcrumb.home'), 'url' => $this->url('/')],
            ['name' => __('seo.breadcrumb.journal'), 'url' => $this->url('/journal')],
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

    private function url(string $path): string
    {
        return rtrim((string) config('app.url'), '/').$path;
    }
}
