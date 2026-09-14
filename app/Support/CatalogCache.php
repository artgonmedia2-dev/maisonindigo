<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Clés de cache du catalogue. Un numéro de version, incrémenté à chaque
 * sauvegarde produit, invalide toutes les listes d'un coup.
 */
final class CatalogCache
{
    public const VERSION_KEY = 'catalog.version';

    public static function version(): int
    {
        return (int) Cache::rememberForever(self::VERSION_KEY, fn (): int => 1);
    }

    public static function bump(): void
    {
        Cache::forever(self::VERSION_KEY, self::version() + 1);
    }

    /**
     * @param  array<string, mixed>  $parts
     */
    public static function key(string $name, array $parts = []): string
    {
        ksort($parts);

        return sprintf('catalog:%d:%s:%s', self::version(), $name, md5((string) json_encode($parts)));
    }
}
