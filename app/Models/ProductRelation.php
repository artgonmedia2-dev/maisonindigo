<?php

namespace App\Models;

use Database\Factories\ProductRelationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductRelation extends Model
{
    /** @use HasFactory<ProductRelationFactory> */
    use HasFactory;

    public const TYPE_COMPLETE_LOOK = 'complete_look';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'related_id',
        'type',
        'position',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function related(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'related_id');
    }
}
