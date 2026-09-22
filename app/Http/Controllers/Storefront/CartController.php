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
use Illuminate\Support\Facades\DB;
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
        $lines = $request->lines();
        $cart = $this->resolveCart->handle($request, create: true);

        $dernier = null;

        try {
            // Un lot part en une transaction : si la seconde taille manque, la
            // première ne reste pas seule dans le panier.
            DB::transaction(function () use ($lines, $cart, $addToCart, &$dernier): void {
                foreach ($lines as $line) {
                    /** @var ProductVariant $variant */
                    $variant = ProductVariant::query()->with('product')->findOrFail($line['variant_id']);

                    $addToCart->handle($cart, $variant, $line['qty']);
                    $dernier = $variant->id;
                }
            });
        } catch (OutOfStockException $exception) {
            return back()->withErrors(['variant_id' => $exception->getMessage()]);
        }

        return back()->with('cart_added', $dernier);
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
