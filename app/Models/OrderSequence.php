<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Compteur par année pour les numéros de commande MI-{AAAA}-{NNNNNN}.
 * Incrémenté dans une transaction avec verrou (Sprint 3).
 */
class OrderSequence extends Model
{
    protected $table = 'order_sequences';

    protected $primaryKey = 'year';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'year',
        'last_number',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'last_number' => 'integer',
        ];
    }

    public static function formatNumber(int $year, int $number): string
    {
        return sprintf('MI-%d-%06d', $year, $number);
    }
}
