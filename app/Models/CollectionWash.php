<?php

namespace App\Models;

use App\Support\CatalogCache;
use Database\Factories\CollectionWashFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Une sous-collection par lavage : /homme/jean-baggy/noir.
 *
 * Seuls quatre lavages sont indexables — la démultiplication de pages de
 * facette est le premier réflexe qui dilue un cluster. Toute autre valeur
 * renvoie 404, elle reste accessible en filtre de la grille.
 *
 * @property int $id
 * @property int $collection_id
 * @property string $wash
 * @property string|null $intro
 * @property list<array{question: string, answer: string}>|null $faq
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property bool $is_visible
 * @property int $position
 */
class CollectionWash extends Model
{
    /** @use HasFactory<CollectionWashFactory> */
    use HasFactory;

    /**
     * Les quatre lavages qui méritent leur propre adresse, et eux seuls.
     *
     * La clé est le segment d'adresse tel que les gens le cherchent, la
     * valeur l'identifiant du lavage au catalogue : « bleu » est le mot que
     * l'on tape, « stone » le nom que porte la toile chez nous.
     *
     * @var array<string, string>
     */
    public const INDEXABLE = [
        'noir' => 'noir',
        'bleu' => 'stone',
        'brut' => 'brut',
        'clair' => 'clair',
    ];

    /** L'identifiant de lavage servi par ce segment d'adresse. */
    public static function washFor(string $segment): ?string
    {
        return self::INDEXABLE[$segment] ?? null;
    }

    /** Le segment d'adresse qui sert ce lavage, s'il en a un. */
    public static function segmentFor(string $wash): ?string
    {
        $segment = array_search($wash, self::INDEXABLE, true);

        return $segment === false ? null : $segment;
    }

    protected $fillable = [
        'collection_id',
        'wash',
        'intro',
        'faq',
        'meta_title',
        'meta_description',
        'is_visible',
        'position',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'faq' => 'array',
            'is_visible' => 'boolean',
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
     * @return BelongsTo<Collection, $this>
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * @param  Builder<CollectionWash>  $query
     */
    public function scopeVisible(Builder $query): void
    {
        $query->where('is_visible', true);
    }
}
