<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Support\CatalogCache;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Un avis client.
 *
 * « Achat vérifié » n'est pas un champ : il se déduit d'une commande livrée
 * rattachée à l'avis. Personne ne peut cocher la mention à la main, ni dans le
 * back-office ni ailleurs.
 *
 * @property int $id
 * @property string $author_name
 * @property string|null $city
 * @property int $rating
 * @property string $body
 * @property string|null $size_bought
 * @property int|null $product_id
 * @property int|null $order_id
 * @property Carbon|null $published_at
 * @property int $position
 */
class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory;

    protected $fillable = [
        'author_name',
        'city',
        'rating',
        'body',
        'size_bought',
        'product_id',
        'order_id',
        'published_at',
        'position',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'position' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => CatalogCache::bump());
        static::deleted(fn () => CatalogCache::bump());
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @param  Builder<Review>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /**
     * Vrai seulement si une commande livrée porte cet avis. La mention se
     * mérite, elle ne se saisit pas.
     */
    public function isVerified(): bool
    {
        $this->loadMissing('order');

        return $this->order !== null && $this->order->status === OrderStatus::Delivered;
    }
}
