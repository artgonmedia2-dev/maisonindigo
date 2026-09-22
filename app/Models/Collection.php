<?php

namespace App\Models;

use App\Enums\CollectionType;
use App\Enums\Gender;
use App\Enums\HomeSlot;
use Database\Factories\CollectionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Collection extends Model implements HasMedia
{
    /** @use HasFactory<CollectionFactory> */
    use HasFactory, InteractsWithMedia;

    public const MEDIA_COVER = 'cover';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'title',
        'description',
        'type',
        'rules',
        'position',
        'is_visible',
        'home_slot',
        'home_badge',
        'hub_gender',
        'hub_cut',
        'intro',
        'content_blocks',
        'faq',
        'meta_title',
        'meta_description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CollectionType::class,
            'home_slot' => HomeSlot::class,
            'hub_gender' => Gender::class,
            'content_blocks' => 'array',
            'faq' => 'array',
            'rules' => 'array',
            'position' => 'integer',
            'is_visible' => 'boolean',
        ];
    }

    /**
     * Les sous-collections par lavage servies sous ce hub.
     *
     * @return HasMany<CollectionWash, $this>
     */
    public function washPages(): HasMany
    {
        return $this->hasMany(CollectionWash::class)->orderBy('position');
    }

    /**
     * @return BelongsToMany<Product, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('position')
            ->orderByPivot('position');
    }

    /**
     * @param  Builder<Collection>  $query
     * @return Builder<Collection>
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true)->orderBy('position');
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
