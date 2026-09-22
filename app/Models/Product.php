<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\ProductStatus;
use App\Support\CatalogTerms;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Imagick;
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
        $termes = app(CatalogTerms::class);

        // Une coupe retirée du vocabulaire ne doit pas casser les SKU déjà
        // émis : on retombe sur les trois premières lettres de l'identifiant.
        $coupe = $termes->cut($this->cut);
        $lavage = $termes->wash($this->wash);

        return implode('-', [
            'MI',
            $this->gender->skuCode(),
            $coupe === null ? Str::upper(Str::substr($this->cut, 0, 3)) : $coupe->sku_code,
            $lavage === null ? Str::upper(Str::substr($this->wash, 0, 3)) : $lavage->sku_code,
        ]);
    }

    /**
     * Titre de la maison : « {Coupe} {Lavage} ». Le genre reste un champ.
     */
    public static function composeTitle(?string $cut, ?string $wash): string
    {
        $termes = app(CatalogTerms::class);
        $coupe = $termes->cut($cut);
        $lavage = $termes->wash($wash);

        if ($coupe === null || $lavage === null) {
            return '';
        }

        return "{$coupe->name} {$lavage->name}";
    }

    /**
     * Vrai si le titre est encore l'assemblage « Coupe Lavage » d'un couple du
     * vocabulaire. Un titre écrit à la main ne doit jamais être écrasé quand la
     * coupe ou le lavage change.
     */
    public static function isComposedTitle(?string $title): bool
    {
        if (blank($title)) {
            return true;
        }

        $termes = app(CatalogTerms::class);

        foreach ($termes->cuts() as $coupe) {
            foreach ($termes->washes() as $lavage) {
                if ($title === "{$coupe->name} {$lavage->name}") {
                    return true;
                }
            }
        }

        return false;
    }

    /** Libellé de la coupe, pour l'affichage. */
    public function cutLabel(): string
    {
        return app(CatalogTerms::class)->cutLabel($this->cut);
    }

    /** Libellé du lavage, pour l'affichage. */
    public function washLabel(): string
    {
        return app(CatalogTerms::class)->washLabel($this->wash);
    }

    public function isActive(): bool
    {
        return $this->status === ProductStatus::Active;
    }

    /**
     * Le serveur sait-il écrire de l'AVIF ?
     */
    public static function supportsAvif(): bool
    {
        if (config('media-library.image_driver') === 'imagick') {
            return class_exists(Imagick::class) && Imagick::queryFormats('AVIF') !== [];
        }

        return function_exists('imageavif');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_GALLERY)
            ->useDisk(config('media-library.disk_name'))
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

        // L'AVIF n'est pas produit partout : une conversion impossible échouerait
        // à chaque envoi, et le <source> correspondant casserait l'affichage.
        if (self::supportsAvif()) {
            $this->addMediaConversion('card-avif')
                ->performOnCollections(self::MEDIA_GALLERY)
                ->withResponsiveImages()
                ->format('avif')
                ->width(800);
        }

        $this->addMediaConversion('zoom')
            ->performOnCollections(self::MEDIA_GALLERY)
            ->format('webp')
            ->width(1600);
    }
}
