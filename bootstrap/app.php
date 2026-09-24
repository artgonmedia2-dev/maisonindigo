<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\PreventInertiaCaching;
use App\Http\Middleware\TrustProxies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Middleware\TrustProxies as BaseTrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        // Les fichiers lus par les robots n'ont pas besoin du groupe « web ».
        then: function (): void {
            Route::middleware([])->group(__DIR__.'/../routes/seo.php');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Proxys de confiance pilotés par TRUSTED_PROXIES (LiteSpeed, Cloudflare).
        $middleware->replace(BaseTrustProxies::class, TrustProxies::class);

        $middleware->web(append: [
            // Après Inertia : il lit l'en-tête X-Inertia que celui-ci a posé.
            PreventInertiaCaching::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Pages d'erreur dans le ton de la maison. Les 500 restent en clair en local.
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request): Response {
            $status = $response->getStatusCode();
            $inertiaStatuses = app()->environment(['local', 'testing']) ? [403, 404] : [403, 404, 500, 503];

            if ($request->is('admin', 'admin/*') || ! in_array($status, $inertiaStatuses, true)) {
                return $response;
            }

            // Hors route (404), le middleware Inertia n'a pas tourné : on partage les props ici.
            Inertia::share(app(HandleInertiaRequests::class)->share($request));

            return Inertia::render('Error', ['status' => $status])
                ->toResponse($request)
                ->setStatusCode($status);
        });
    })->create();
