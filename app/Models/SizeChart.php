<?php

namespace App\Models;

use App\Enums\Gender;
use Database\Factories\SizeChartFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SizeChart extends Model
{
    /** @use HasFactory<SizeChartFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'gender',
        'cut',
        'title',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
        ];
    }

    /**
     * @return HasMany<SizeChartRow, $this>
     */
    public function rows(): HasMany
    {
        return $this->hasMany(SizeChartRow::class)->orderBy('size');
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
