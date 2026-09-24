<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Empêche qu'une réponse Inertia JSON soit resservie à une navigation normale.
 *
 * Inertia sert deux choses à la même adresse : la page HTML complète, et un
 * document JSON pour les navigations internes. Il les distingue par l'en-tête
 * « Vary: X-Inertia », que les caches sont censés respecter.
 *
 * LiteSpeed, sur cet hébergement, remplace « Vary » par « Accept-Encoding »
 * au lieu de le compléter : l'information disparaît, et le cache du navigateur
 * finit par afficher le JSON brut à la place de la boutique.
 *
 * On ne peut pas compter sur « Vary » ici. On interdit donc purement et
 * simplement le stockage des réponses JSON d'Inertia, et on réaffirme « Vary »
 * pour les caches qui, eux, le respectent.
 */
class PreventInertiaCaching
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $vary = $response->headers->get('Vary');

        if ($vary === null || ! str_contains($vary, 'X-Inertia')) {
            $response->headers->set('Vary', trim('X-Inertia, '.($vary ?? ''), ', '));
        }

        if ($response->headers->has('X-Inertia')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
        }

        return $response;
    }
}
