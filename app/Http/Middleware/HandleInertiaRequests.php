<?php

namespace App\Http\Middleware;

use App\Actions\Cart\ResolveCart;
use App\Actions\Cart\SummarizeCart;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * @var string
     */
    protected $rootView = 'app';

    public function __construct(
        private readonly ResolveCart $resolveCart,
        private readonly SummarizeCart $summarizeCart,
    ) {}

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Props partagées avec toutes les pages.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user === null ? null : [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                ],
            ],
            'maison' => [
                'name' => config('maison.name'),
                'tagline' => config('maison.tagline'),
                'contact' => [
                    'email' => config('maison.contact.email'),
                    'whatsapp' => config('maison.contact.whatsapp'),
                    'city' => config('maison.contact.city'),
                ],
            ],
            'cart' => fn (): array => $this->summarizeCart->handle(
                $this->resolveCart->handle($request),
                $request->hasSession() ? $request->session()->get('discount_code') : null,
            ),
            'flash' => [
                'success' => fn () => $request->hasSession() ? $request->session()->get('success') : null,
                'error' => fn () => $request->hasSession() ? $request->session()->get('error') : null,
                'cart_added' => fn () => $request->hasSession() ? $request->session()->get('cart_added') : null,
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
