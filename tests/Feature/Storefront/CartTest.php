<?php

use App\Models\CartItem;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductVariant;
use Inertia\Testing\AssertableInertia as Assert;

function variantInStock(int $stock = 5, int $price = 49900): ProductVariant
{
    $product = Product::factory()->create(['price' => $price]);

    return ProductVariant::factory()->for($product)->create(['size' => 32, 'length' => 32, 'stock' => $stock]);
}

it('ajoute une variante au panier et la partage avec toutes les pages', function () {
    $variant = variantInStock();

    $this->from($variant->product->path())
        ->post(route('cart.store'), ['variant_id' => $variant->id, 'qty' => 1])
        ->assertRedirect($variant->product->path())
        ->assertSessionHas('cart_added', $variant->id);

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Cart/Index')
            ->where('cart.count', 1)
            ->where('cart.subtotal', 49900)
            ->where('cart.total', 49900)
            ->where('cart.items.0.title', $variant->product->title)
            ->where('cart.items.0.size', 32)
            ->where('cart.items.0.unit_price', 49900)
        );
});

it('cumule les ajouts d’une même variante et respecte le stock', function () {
    $variant = variantInStock(stock: 2);

    $this->post(route('cart.store'), ['variant_id' => $variant->id]);
    $this->post(route('cart.store'), ['variant_id' => $variant->id]);
    $this->post(route('cart.store'), ['variant_id' => $variant->id])->assertSessionHasErrors('variant_id');

    expect(CartItem::query()->sum('qty'))->toBe(2);
});

it('modifie la quantité et retire une ligne', function () {
    $variant = variantInStock();
    $this->post(route('cart.store'), ['variant_id' => $variant->id]);
    $item = CartItem::query()->firstOrFail();

    $this->patch(route('cart.update', $item), ['qty' => 3])->assertRedirect();
    expect($item->refresh()->qty)->toBe(3);

    $this->patch(route('cart.update', $item), ['qty' => 9])->assertSessionHasErrors('qty');

    $this->delete(route('cart.destroy', $item))->assertRedirect();
    expect(CartItem::query()->count())->toBe(0);
});

it('refuse de toucher au panier d’une autre session', function () {
    $variant = variantInStock();
    $this->post(route('cart.store'), ['variant_id' => $variant->id]);
    $item = CartItem::query()->firstOrFail();

    $this->flushSession();

    $this->patch(route('cart.update', $item), ['qty' => 2])->assertNotFound();
});

it('applique la remise automatique deux jeans dix pour cent', function () {
    Discount::factory()->bundle()->create();
    $variant = variantInStock();

    $this->post(route('cart.store'), ['variant_id' => $variant->id, 'qty' => 2]);

    $this->get(route('cart.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('cart.subtotal', 99800)
            ->where('cart.discount.total', 9980)
            ->where('cart.discount.label', 'Deux jeans, dix pour cent')
            ->where('cart.total', 89820)
        );
});

it('refuse un produit hors ligne', function () {
    $product = Product::factory()->draft()->create();
    $variant = ProductVariant::factory()->for($product)->create(['stock' => 5]);

    $this->post(route('cart.store'), ['variant_id' => $variant->id])->assertSessionHasErrors('variant_id');
});
