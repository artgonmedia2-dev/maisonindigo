<?php

namespace App\Models;

use App\Enums\DiscountType;
use Database\Factories\DiscountFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Discount extends Model
{
    /** @use HasFactory<DiscountFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'type',
        'value',
        'rules',
        'starts_at',
        'ends_at',
        'usage_limit',
        'usage_count',
        'active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => DiscountType::class,
            'value' => 'integer',
            'rules' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'usage_limit' => 'integer',
            'usage_count' => 'integer',
            'active' => 'boolean',
        ];
    }

    /**
     * Remise appliquée automatiquement, sans code.
     */
    public function isAutomatic(): bool
    {
        return $this->code === null;
    }

    public function isCurrent(?Carbon $at = null): bool
    {
        $at ??= now();

        if (! $this->active) {
            return false;
        }

        if ($this->starts_at !== null && $this->starts_at->isAfter($at)) {
            return false;
        }

        if ($this->ends_at !== null && $this->ends_at->isBefore($at)) {
            return false;
        }

        if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * @param  Builder<Discount>  $query
     * @return Builder<Discount>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }
}
