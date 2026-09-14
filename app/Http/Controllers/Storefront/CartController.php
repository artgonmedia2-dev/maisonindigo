<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Cart\AddToCart;
use App\Actions\Cart\RemoveCartItem;
use App\Actions\Cart\ResolveCart;
use App\Actions\Cart\UpdateCartItem;
use App\Exceptions\OutOfStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Le résumé du panier est partagé avec toutes les pages (HandleInertiaRequests) ;
 * les actions renvoient donc simplement en arrière.
 */
class CartController extends Controller
{
    public function __construct(private readonly ResolveCart $resolveCart) {}

    public function index(): Response
    {
        return Inertia::render('Cart/Index', [
            'meta' => [
                'title' => __('storefront.cart.title'),
                'description' => null,
            ],
        ]);
    }

    public function store(AddToCartRequest $request, AddToCart $addToCart): RedirectResponse
    {
        /** @var ProductVariant $variant */
        $variant = ProductVariant::query()->with('product')->findOrFail($request->integer('variant_id'));
        $cart = $this->resolveCart->handle($request, create: true);

        try {
            $addToCart->handle($cart, $variant, $request->integer('qty', 1));
        } catch (OutOfStockException $exception) {
            return back()->withErrors(['variant_id' => $exception->getMessage()]);
        }

        return back()->with('cart_added', $variant->id);
    }

    public function update(UpdateCartItemRequest $request, CartItem $item, UpdateCartItem $updateCartItem): RedirectResponse
    {
        $this->authorizeItem($request, $item);

        try {
            $updateCartItem->handle($item, $request->integer('qty'));
        } catch (OutOfStockException $exception) {
            return back()->withErrors(['qty' => $exception->getMessage()]);
        }

        return back();
    }

    public function destroy(Request $request, CartItem $item, RemoveCartItem $removeCartItem): RedirectResponse
    {
        $this->authorizeItem($request, $item);

        $removeCartItem->handle($item);

        return back();
    }

    /**
     * Une ligne ne peut être modifiée que depuis le panier qui la contient.
     */
    private function authorizeItem(Request $request, CartItem $item): void
    {
        $cart = $this->resolveCart->handle($request);

        abort_if($cart === null || $item->cart_id !== $cart->id, 404);
    }
}
