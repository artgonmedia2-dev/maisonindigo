<?php

use App\Enums\DiscountType;
use App\Enums\Gender;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingZone;
use App\Models\SizeChart;
use Database\Seeders\DemoCatalogSeeder;
use Database\Seeders\ShippingZonesSeeder;
use Database\Seeders\SizeChartsSeeder;

it('crée un catalogue de démonstration cohérent et rejouable', function () {
    $this->seed([SizeChartsSeeder::class, ShippingZonesSeeder::class, DemoCatalogSeeder::class]);

    expect(Product::query()->count())->toBe(13)
        ->and(Product::query()->active()->count())->toBe(13)
        ->and(Product::query()->where('gender', Gender::Homme)->count())->toBe(8)
        // Trois baggys homme : la tête du cluster « jean baggy homme ».
        ->and(Product::query()->where('cut', 'baggy')->count())->toBe(3)
        ->and(Product::query()->where('gender', Gender::Femme)->count())->toBe(5)
        ->and(Product::query()->where('is_new', true)->count())->toBeGreaterThanOrEqual(3)
        ->and(Product::query()->where('is_atelier', true)->count())->toBe(2)
        ->and(Product::query()->whereNull('size_chart_id')->count())->toBe(0)
        ->and(ProductVariant::query()->count())->toBe(13 * 8 * 3)
        ->and(ProductVariant::query()->where('stock', 0)->count())->toBeGreaterThan(0)
        ->and(ProductVariant::query()->where('sku', 'MI-H-STR-BRU-32-32')->exists())->toBeTrue()
        ->and(SizeChart::query()->count())->toBe(13)
        ->and(ShippingZone::query()->count())->toBe(6)
        ->and(Discount::query()->where('type', DiscountType::Bundle)->whereNull('code')->count())->toBe(1);

    $product = Product::query()->where('slug', 'straight-indigo-brut-homme')->firstOrFail();
    expect($product->title)->toBe('Straight Indigo Brut')
        ->and($product->price)->toBe(49900)
        ->and($product->title)->not->toContain('Maison Indigo');

    // Rejouer le seeder ne duplique rien.
    $this->seed([SizeChartsSeeder::class, ShippingZonesSeeder::class, DemoCatalogSeeder::class]);

    expect(Product::query()->count())->toBe(13)
        ->and(ProductVariant::query()->count())->toBe(312)
        ->and(ShippingZone::query()->count())->toBe(6)
        ->and(Discount::query()->count())->toBe(1);
});
