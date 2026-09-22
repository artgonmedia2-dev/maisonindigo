<?php

namespace App\Models;

use App\Enums\Gender;
use App\Support\CatalogTerms;
use Database\Factories\CutFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Une coupe du vocabulaire de la maison : Straight, Wide Leg, Bootcut.
 *
 * L'identifiant court (« wide_leg ») voyage dans les adresses de la boutique
 * et dans les SKU déjà émis : il ne se change plus une fois des produits créés.
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string $sku_code
 * @property list<string> $genders
 * @property int $position
 * @property bool $is_active
 *
 * @use HasFactory<CutFactory>
 */
class Cut extends Model
{
    /** @use HasFactory<CutFactory> */
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'sku_code',
        'genders',
        'position',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'genders' => 'array',
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
        return $this->hasMany(Product::class, 'cut', 'slug');
    }

    /** Un terme employé par un produit ne se supprime plus. */
    public function isUsed(): bool
    {
        return $this->products()->exists();
    }

    /**
     * @param  Builder<Cut>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<Cut>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('position')->orderBy('name');
    }

    /**
     * Le segment d'adresse de la coupe : « wide_leg » s'écrit « wide-leg »
     * dans /homme/jean-wide-leg. Les identifiants gardent leur souligné, qui
     * voyage dans les SKU.
     */
    public function urlSegment(): string
    {
        return str_replace('_', '-', $this->slug);
    }

    public function isAvailableFor(Gender $gender): bool
    {
        return in_array($gender->value, $this->genders, true);
    }

    /**
     * Les coupes proposées pour un genre, dans l'ordre choisi par la maison.
     *
     * @return Collection<int, Cut>
     */
    public static function forGender(?Gender $gender): Collection
    {
        /** @var Collection<int, Cut> $cuts */
        $cuts = static::query()->active()->ordered()->get();

        return $gender === null
            ? $cuts
            : $cuts->filter(fn (Cut $cut): bool => $cut->isAvailableFor($gender))->values();
    }
}
