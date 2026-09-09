<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
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
