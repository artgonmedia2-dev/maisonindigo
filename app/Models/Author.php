<?php

namespace App\Models;

use Database\Factories\AuthorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * L'auteur d'un article. Un nom identifiable et une page dédiée : les moteurs
 * génératifs citent plus volontiers une source signée.
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string|null $role
 * @property string|null $bio
 */
class Author extends Model implements HasMedia
{
    /** @use HasFactory<AuthorFactory> */
    use HasFactory;

    use InteractsWithMedia;

    public const MEDIA_PORTRAIT = 'portrait';

    protected $fillable = ['slug', 'name', 'role', 'bio', 'email'];

    /**
     * @return HasMany<Article, $this>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_PORTRAIT)
            ->useDisk(config('media-library.disk_name'))
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }
}
