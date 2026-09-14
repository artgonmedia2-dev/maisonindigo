<?php

namespace App\Actions\Cart;

use App\Models\Cart;
use App\Models\Customer;
use Illuminate\Http\Request;

/**
 * Retrouve le panier de la requête : par identifiant en session, sinon par client connecté.
 * Ne crée un panier que si `$create` est vrai (ajout d'un article).
 */
class ResolveCart
{
    public const SESSION_KEY = 'cart_id';

    public function handle(Request $request, bool $create = false): ?Cart
    {
        /** @var Customer|null $customer */
        $customer = $request->user();
        $session = $request->hasSession() ? $request->session() : null;

        $cart = null;
        $cartId = $session?->get(self::SESSION_KEY);

        if (is_int($cartId) || is_string($cartId)) {
            $cart = Cart::query()->with('items.variant.product')->find((int) $cartId);
        }

        if ($cart === null && $customer !== null) {
            $cart = Cart::query()->with('items.variant.product')->where('customer_id', $customer->id)->latest('id')->first();
        }

        if ($cart === null && $create) {
            $cart = Cart::query()->create([
                'session_id' => $session?->getId(),
                'customer_id' => $customer?->id,
            ]);
            $cart->setRelation('items', $cart->items()->get());
        }

        if ($cart === null) {
            return null;
        }

        if ($customer !== null && $cart->customer_id === null) {
            $cart->forceFill(['customer_id' => $customer->id])->save();
        }

        $session?->put(self::SESSION_KEY, $cart->id);

        return $cart;
    }

    public function forget(Request $request): void
    {
        if ($request->hasSession()) {
            $request->session()->forget(self::SESSION_KEY);
        }
    }
}
