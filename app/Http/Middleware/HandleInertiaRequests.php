<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * @var string
     */
    protected $rootView = 'app';

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
            // Sprint 2 : nombre d'articles du panier
            'cart' => [
                'count' => 0,
            ],
            'flash' => [
                'success' => fn () => $request->hasSession() ? $request->session()->get('success') : null,
                'error' => fn () => $request->hasSession() ? $request->session()->get('error') : null,
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
