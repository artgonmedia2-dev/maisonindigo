<?php

namespace App\Models;

use Database\Factories\ShippingZoneFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ShippingZone extends Model
{
    /** @use HasFactory<ShippingZoneFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'cities',
        'delay',
        'position',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cities' => 'array',
            'position' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<ShippingRate, $this>
     */
    public function rates(): HasMany
    {
        return $this->hasMany(ShippingRate::class);
    }

    /**
     * Tarif en vigueur (un seul tarif par zone au MVP).
     *
     * @return HasOne<ShippingRate, $this>
     */
    public function rate(): HasOne
    {
        return $this->hasOne(ShippingRate::class)->latestOfMany();
    }

    /**
     * @return HasMany<Address, $this>
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function servesCity(string $city): bool
    {
        $needle = mb_strtolower(trim($city));

        foreach ($this->cities as $known) {
            if (mb_strtolower((string) $known) === $needle) {
                return true;
            }
        }

        return false;
    }
}
