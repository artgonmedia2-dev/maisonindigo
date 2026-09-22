<?php

namespace App\Models;

use App\Support\CatalogCache;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Un article du Journal.
 *
 * « En bref » porte la réponse directe en deux ou trois phrases : c'est le
 * fragment que les moteurs génératifs reprennent, il vient avant tout récit.
 *
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string $excerpt
 * @property list<array{title: string, body: string}>|null $content_blocks
 * @property list<array{question: string, answer: string}>|null $faq
 * @property int|null $author_id
 * @property Carbon|null $published_at
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property int $position
 */
class Article extends Model implements HasMedia
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory;

    use InteractsWithMedia;

    public const MEDIA_COVER = 'cover';

    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'content_blocks',
        'faq',
        'author_id',
        'published_at',
        'meta_title',
        'meta_description',
        'position',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'content_blocks' => 'array',
            'faq' => 'array',
            'published_at' => 'datetime',
            'position' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Le plan du site et les listes en cache suivent le contenu.
        static::saved(fn () => CatalogCache::bump());
        static::deleted(fn () => CatalogCache::bump());
    }

    /**
     * @return BelongsTo<Author, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    /**
     * Les trois produits de l'encadré, dans l'ordre choisi.
     *
     * @return BelongsToMany<Product, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('position')
            ->orderBy('article_product.position');
    }

    /**
     * Un article sans date de publication reste un brouillon.
     *
     * @param  Builder<Article>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COVER)
            ->useDisk(config('media-library.disk_name'))
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('cover')
            ->performOnCollections(self::MEDIA_COVER)
            ->withResponsiveImages()
            ->format('webp')
            ->width(1600);
    }
}
