<?php

namespace App\Models;

use Database\Factories\SizeChartRowFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SizeChartRow extends Model
{
    /** @use HasFactory<SizeChartRowFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'size_chart_id',
        'size',
        'waist_cm',
        'hips_cm',
        'thigh_cm',
        'inseam_30',
        'inseam_32',
        'inseam_34',
        'position',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'waist_cm' => 'decimal:1',
            'hips_cm' => 'decimal:1',
            'thigh_cm' => 'decimal:1',
            'inseam_30' => 'decimal:1',
            'inseam_32' => 'decimal:1',
            'inseam_34' => 'decimal:1',
            'position' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<SizeChart, $this>
     */
    public function sizeChart(): BelongsTo
    {
        return $this->belongsTo(SizeChart::class);
    }
}
