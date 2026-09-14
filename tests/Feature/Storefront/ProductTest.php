<?php

use App\Enums\Cut;
use App\Enums\Gender;
use App\Enums\Wash;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SizeChart;
use App\Models\SizeChartRow;
use App\Models\StockAlert;
use Inertia\Testing\AssertableInertia as Assert;

it('affiche une fiche produit avec ses variantes et son tableau de mesures', function () {
    $chart = SizeChart::factory()->create(['gender' => Gender::Homme, 'cut' => Cut::Straight]);
    SizeChartRow::factory()->for($chart)->create(['size' => 32]);

    $product = Product::factory()->withVariants(sizes: [30, 32, 34])->create([
        'gender' => Gender::Homme, 'cut' => Cut::Straight, 'wash' => Wash::Brut, 'size_chart_id' => $chart->id,
    ]);
    Product::factory()->withVariants()->create(['gender' => Gender::Homme, 'cut' => Cut::Slim, 'wash' => Wash::Noir]);

    $this->get(route('product.show', $product))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Product/Show')
            ->where('product.title', 'Straight Indigo Brut')
            ->where('product.sizes', [30, 32, 34])
            ->where('product.lengths', [30, 32, 34])
            ->has('product.variants', 9)
            ->where('product.variants.0.sku', 'MI-H-STR-BRU-30-30')
            ->has('product.size_chart', 1)
            ->where('product.size_chart.0.size', 32)
            ->where('product.images', [])
            ->has('related', 1)
            ->where('related.0.title', 'Slim Noir')
            ->where('meta.title', 'Straight Indigo Brut Homme')
        );
});

it('signale une taille en rupture sans la masquer', function () {
    $product = Product::factory()->create(['gender' => Gender::Femme, 'cut' => Cut::Mom, 'wash' => Wash::Clair]);
    ProductVariant::factory()->for($product)->create(['size' => 28, 'length' => 32, 'stock' => 0]);
    ProductVariant::factory()->for($product)->create(['size' => 30, 'length' => 32, 'stock' => 2]);

    $this->get(route('product.show', $product))
        ->assertInertia(fn (Assert $page) => $page
            ->where('product.variants.0.in_stock', false)
            ->where('product.variants.1.in_stock', true)
            ->where('product.variants.1.low_stock', true)
            ->where('product.in_stock', true)
        );
});

it('renvoie 404 pour un brouillon', function () {
    $product = Product::factory()->draft()->create();

    $this->get(route('product.show', $product))->assertNotFound();
});

it('enregistre une alerte de retour en stock', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->for($product)->outOfStock()->create();

    $this->from(route('product.show', $product))
        ->post(route('stock-alerts.store'), ['variant_id' => $variant->id, 'email' => 'salma@exemple.ma'])
        ->assertRedirect(route('product.show', $product))
        ->assertSessionHas('success');

    expect(StockAlert::query()->where('product_variant_id', $variant->id)->where('email', 'salma@exemple.ma')->count())->toBe(1);

    // Une deuxième demande identique ne crée pas de doublon.
    $this->post(route('stock-alerts.store'), ['variant_id' => $variant->id, 'email' => 'salma@exemple.ma']);
    expect(StockAlert::query()->count())->toBe(1);
});

it('exige un e-mail ou un mobile pour l’alerte', function () {
    $variant = ProductVariant::factory()->create();

    $this->post(route('stock-alerts.store'), ['variant_id' => $variant->id])
        ->assertSessionHasErrors(['email', 'phone']);
});
