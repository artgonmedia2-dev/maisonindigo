<?php

namespace App\Models;

use App\Support\CatalogTerms;
use Database\Factories\WashFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Un lavage du vocabulaire de la maison : Indigo Brut, Stone, Écru.
 *
 * Comme pour la coupe, l'identifiant court voyage dans les adresses et les
 * SKU : il se fige dès qu'un produit l'utilise.
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string $sku_code
 * @property int $position
 * @property bool $is_active
 *
 * @use HasFactory<WashFactory>
 */
class Wash extends Model
{
    /** @use HasFactory<WashFactory> */
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'sku_code',
        'position',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Le registre du vocabulaire ne doit pas survivre à une modification.
        static::saved(fn () => app(CatalogTerms::class)->forget());
        static::deleted(fn () => app(CatalogTerms::class)->forget());
    }

    /**
     * Les produits qui emploient ce terme. La clé est l'identifiant court,
     * pas la clé primaire : c'est lui que les produits stockent.
     *
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'wash', 'slug');
    }

    /** Un terme employé par un produit ne se supprime plus. */
    public function isUsed(): bool
    {
        return $this->products()->exists();
    }

    /**
     * @param  Builder<Wash>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<Wash>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('position')->orderBy('name');
    }
}
