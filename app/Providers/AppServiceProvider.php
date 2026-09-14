<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Observers\ProductObserver;
use App\Observers\ProductVariantObserver;
use App\Observers\ShippingZoneObserver;
use App\Services\ShippingCalculator;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Derrière un proxy TLS (Hostinger, Cloudflare), toutes les URL en https.
        if (config('maison.force_https') === true) {
            URL::forceScheme('https');
        }

        // Pas de N+1 : le chargement paresseux lève une exception hors production.
        Model::preventLazyLoading(! $this->app->environment('production'));
        Model::preventSilentlyDiscardingAttributes(! $this->app->environment('production'));

        Product::observe(ProductObserver::class);
        ProductVariant::observe(ProductVariantObserver::class);
        ShippingZone::observe(ShippingZoneObserver::class);
        ShippingRate::saved(fn () => ShippingCalculator::forget());

        $this->configureRateLimiting();
    }

    /**
     * Limites par adresse IP sur les parcours sensibles.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('checkout', fn (Request $request) => Limit::perMinute(5)->by($request->ip() ?? 'inconnu'));
        RateLimiter::for('cart', fn (Request $request) => Limit::perMinute(60)->by($request->ip() ?? 'inconnu'));
        RateLimiter::for('stock-alert', fn (Request $request) => Limit::perMinute(5)->by($request->ip() ?? 'inconnu'));
        RateLimiter::for('size-quiz', fn (Request $request) => Limit::perMinute(20)->by($request->ip() ?? 'inconnu'));
    }
}
