<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockAlertRequest;
use App\Models\StockAlert;
use Illuminate\Http\RedirectResponse;

/**
 * « Me prévenir » sur une taille en rupture.
 */
class StockAlertController extends Controller
{
    public function store(StockAlertRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        StockAlert::query()->firstOrCreate(
            [
                'product_variant_id' => (int) $validated['variant_id'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'notified_at' => null,
            ],
        );

        return back()->with('success', __('storefront.product.alert_registered'));
    }
}
