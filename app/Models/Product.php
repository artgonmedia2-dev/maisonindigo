<?php

namespace App\Models;

use App\Enums\Cut;
use App\Enums\Gender;
use App\Enums\ProductStatus;
use App\Enums\Wash;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Produit = 1 coupe × 1 lavage × 1 genre. Titre « {Coupe} {Lavage} ».
 */
class Product extends Model implements HasMedia
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, InteractsWithMedia;

    public const MEDIA_GALLERY = 'gallery';

    /** Les 6 vues, dans l'ordre. */
    public const VIEWS = ['face', 'dos', 'profil', 'tissu', 'detail', 'porte'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'title',
        'gender',
        'cut',
        'wash',
        'description',
        'price',
        'compare_at_price',
        'fabric_origin',
        'weight_oz',
        'composition',
        'model_height_cm',
        'model_size',
        'size_advice',
        'size_chart_id',
        'status',
        'is_new',
        'is_featured',
        'is_atelier',
        'meta_title',
        'meta_description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'cut' => Cut::class,
            'wash' => Wash::class,
            'status' => ProductStatus::class,
            'price' => 'integer',
            'compare_at_price' => 'integer',
            'weight_oz' => 'decimal:1',
            'model_height_cm' => 'integer',
            'is_new' => 'boolean',
            'is_featured' => 'boolean',
            'is_atelier' => 'boolean',
        ];
    }

    /**
     * @return HasMany<ProductVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('position')->orderBy('size')->orderBy('length');
    }

    /**
     * @return BelongsTo<SizeChart, $this>
     */
    public function sizeChart(): BelongsTo
    {
        return $this->belongsTo(SizeChart::class);
    }

    /**
     * @return BelongsToMany<Collection, $this>
     */
    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class)->withPivot('position');
    }

    /**
     * Produits liés « Complète le look ».
     *
     * @return BelongsToMany<Product, $this>
     */
    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_relations', 'product_id', 'related_id')
            ->withPivot(['type', 'position'])
            ->withTimestamps()
            ->orderByPivot('position');
    }

    /**
     * @return HasMany<ProductRelation, $this>
     */
    public function relations(): HasMany
    {
        return $this->hasMany(ProductRelation::class);
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::Active);
    }

    /**
     * Préfixe SKU commun aux variantes : MI-{genre}-{coupe}-{lavage}.
     */
    public function skuPrefix(): string
    {
        return implode('-', ['MI', $this->gender->skuCode(), $this->cut->skuCode(), $this->wash->skuCode()]);
    }

    public function isActive(): bool
    {
        return $this->status === ProductStatus::Active;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_GALLERY)
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // Ratio 4:5, fond écru. Conversions WebP et AVIF, images responsives.
        $this->addMediaConversion('card')
            ->performOnCollections(self::MEDIA_GALLERY)
            ->withResponsiveImages()
            ->format('webp')
            ->width(800);

        $this->addMediaConversion('card-avif')
            ->performOnCollections(self::MEDIA_GALLERY)
            ->withResponsiveImages()
            ->format('avif')
            ->width(800);

        $this->addMediaConversion('zoom')
            ->performOnCollections(self::MEDIA_GALLERY)
            ->format('webp')
            ->width(1600);
    }
}
