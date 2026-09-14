<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;

/**
 * Proxys de confiance lus depuis la configuration (TRUSTED_PROXIES) :
 * « * » derrière LiteSpeed/Hostinger ou Cloudflare, vide en local.
 * Nécessaire pour que Laravel voie le HTTPS et l'adresse IP réelle du client.
 */
class TrustProxies extends Middleware
{
    /**
     * @return array<int, string>|string|null
     */
    protected function proxies()
    {
        $configured = config('maison.trusted_proxies');

        if ($configured === null || $configured === '') {
            return null;
        }

        if ($configured === '*' || $configured === '**') {
            return $configured;
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $configured))));
    }
}
