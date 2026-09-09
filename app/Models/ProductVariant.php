<?php

namespace App\Models;

use Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Variante = taille (26–42) × longueur (30/32/34).
 */
class ProductVariant extends Model
{
    /** @use HasFactory<ProductVariantFactory> */
    use HasFactory;

    /** @var list<int> */
    public const SIZES = [26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42];

    /** @var list<int> */
    public const LENGTHS = [30, 32, 34];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'size',
        'length',
        'sku',
        'stock',
        'low_stock_threshold',
        'position',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'length' => 'integer',
            'stock' => 'integer',
            'low_stock_threshold' => 'integer',
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
     * @return HasMany<StockAlert, $this>
     */
    public function stockAlerts(): HasMany
    {
        return $this->hasMany(StockAlert::class);
    }

    /**
     * @return HasMany<CartItem, $this>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public static function buildSku(Product $product, int $size, int $length): string
    {
        return "{$product->skuPrefix()}-{$size}-{$length}";
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    public function isLowStock(): bool
    {
        return $this->stock > 0 && $this->stock <= $this->low_stock_threshold;
    }

    /**
     * Libellé « 32 / 32 ».
     */
    public function label(): string
    {
        return "{$this->size} / {$this->length}";
    }
}
