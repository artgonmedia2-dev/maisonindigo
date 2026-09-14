<?php

namespace App\Observers;

use App\Models\Product;
use App\Support\CatalogCache;

/**
 * Invalide les collections en cache à chaque sauvegarde produit.
 */
class ProductObserver
{
    public function saved(Product $product): void
    {
        CatalogCache::bump();
    }

    public function deleted(Product $product): void
    {
        CatalogCache::bump();
    }
}
