<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Settings\ShopSettings;
use Database\Seeders\ShippingZonesSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(ShippingZonesSeeder::class);
    app(ShopSettings::class)->fill(['home_reviews_enabled' => true])->save();
});

it('tait la section tant qu’aucun avis n’est publié', function () {
    Review::factory()->draft()->create();

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('reviews.items', [])
        ->where('reviews.average', null)
        ->where('reviews.count', 0)
    );
});

it('publie les avis et la moyenne de tous', function () {
    Review::factory()->create(['rating' => 5, 'position' => 1]);
    Review::factory()->create(['rating' => 4, 'position' => 2]);
    Review::factory()->draft()->create(['rating' => 1]);

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->has('reviews.items', 2)
        // Le brouillon à 1 étoile ne tire pas la moyenne vers le bas.
        ->where('reviews.average', 4.5)
        ->where('reviews.count', 2)
    );
});

it('ne montre que six avis mais compte tous les publiés', function () {
    Review::factory()->count(9)->create(['rating' => 5]);

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->has('reviews.items', 6)
        ->where('reviews.count', 9)
    );
});

it('ne vérifie un avis que sur une commande livrée', function () {
    $livree = Order::factory()->create(['status' => OrderStatus::Delivered]);
    $expediee = Order::factory()->create(['status' => OrderStatus::Shipped]);

    Review::factory()->create(['order_id' => $livree->id, 'position' => 1]);
    Review::factory()->create(['order_id' => $expediee->id, 'position' => 2]);
    Review::factory()->create(['order_id' => null, 'position' => 3]);

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('reviews.items.0.verified', true)
        ->where('reviews.items.1.verified', false)
        ->where('reviews.items.2.verified', false)
    );
});

it('renvoie vers le produit concerné quand il est renseigné', function () {
    $product = Product::factory()->withVariants()->create();
    Review::factory()->create(['product_id' => $product->id]);

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('reviews.items.0.product.url', $product->path())
        ->where('reviews.items.0.product.title', $product->title)
    );
});

it('retire la section quand la maison la désactive', function () {
    Review::factory()->count(3)->create();
    app(ShopSettings::class)->fill(['home_reviews_enabled' => false])->save();

    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('reviews.items', []));
});
