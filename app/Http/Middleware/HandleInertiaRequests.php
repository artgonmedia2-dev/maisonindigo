<?php

namespace App\Http\Middleware;

use App\Actions\Cart\ResolveCart;
use App\Actions\Cart\SummarizeCart;
use App\Settings\ShopSettings;
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
        private readonly ShopSettings $settings,
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
                // L'entité de marque, une seule source : lang/fr/seo.php. Elle
                // est reprise au mot près dans le schema Organization, le pied
                // de page, la page La maison et llms.txt.
                'entity' => __('seo.entity'),
                'contact' => [
                    'email' => $this->settings->contact_email,
                    'whatsapp' => $this->settings->contact_whatsapp,
                    'city' => $this->settings->contact_city,
                ],
                'exchange_days' => $this->settings->exchange_days,
                'announcement' => $this->settings->announcement_enabled && filled($this->settings->announcement_text)
                    ? $this->settings->announcement_text
                    : null,
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
