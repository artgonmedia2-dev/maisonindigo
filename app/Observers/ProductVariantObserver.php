<?php

namespace App\Observers;

use App\Models\ProductVariant;
use App\Support\CatalogCache;

/**
 * Le stock change la disponibilité affichée dans les grilles : on invalide le cache.
 */
class ProductVariantObserver
{
    public function saved(ProductVariant $variant): void
    {
        CatalogCache::bump();
    }

    public function deleted(ProductVariant $variant): void
    {
        CatalogCache::bump();
    }
}
