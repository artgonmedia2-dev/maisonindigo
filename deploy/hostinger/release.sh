#!/usr/bin/env bash
# ------------------------------------------------------------------
# Maison Indigo — mise en production sur Hostinger (Hébergement Premium)
#
# À exécuter sur le serveur, dans le dossier de l'application, après le
# dépôt des fichiers (rsync depuis GitHub Actions, ou git pull + build
# local). Idempotent : peut être relancé sans risque.
#
#   bash deploy/hostinger/release.sh
# ------------------------------------------------------------------
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
# PHP 8.3 est indispensable. Sur un mutualise, « php » en SSH peut rester en 8.2
# meme quand le site est regle en 8.3 : on cherche alors le bon binaire.
find_php83() {
    local candidate
    for candidate in "${PHP_BIN:-}" php /opt/alt/php83/usr/bin/php /usr/local/bin/php83 /usr/bin/php8.3 /opt/cpanel/ea-php83/root/usr/bin/php; do
        [[ -z "$candidate" ]] && continue
        command -v "$candidate" >/dev/null 2>&1 || continue
        if [[ "$("$candidate" -r 'echo PHP_VERSION_ID >= 80300 ? 1 : 0;' 2>/dev/null)" == "1" ]]; then
            echo "$candidate"
            return 0
        fi
    done
    return 1
}

if ! PHP_BIN="$(find_php83)"; then
    echo "✗ Aucun PHP 8.3 trouve. hPanel → Avance → Configuration PHP, puis cherchez le binaire :" >&2
    echo "    ls /opt/alt/php83/usr/bin/php /usr/local/bin/php8*" >&2
    exit 1
fi

cd "$APP_DIR"

echo "→ Application : $APP_DIR"
echo "→ PHP : $($PHP_BIN -r 'echo PHP_VERSION;')"

if [[ ! -f .env ]]; then
    echo "✗ Aucun fichier .env : copiez .env.production.example vers .env et renseignez-le." >&2
    exit 1
fi

if ! grep -qE '^APP_KEY=base64:' .env; then
    echo "→ Génération de la clé d'application"
    $PHP_BIN artisan key:generate --force --no-interaction
fi

# Arborescence de stockage (exclue de la synchronisation)
mkdir -p storage/app/public storage/framework/{cache/data,sessions,testing,views} storage/logs bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

# Dépendances PHP si le dossier vendor n'a pas été synchronisé
if [[ ! -d vendor ]]; then
    echo "→ composer install (production)"
    composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
fi

if [[ ! -f public/build/manifest.json ]]; then
    echo "✗ public/build/manifest.json absent : lancez « npm run build » avant de déployer (voir docs/Deploiement_Hostinger.md)." >&2
    exit 1
fi

echo "→ Maintenance"
$PHP_BIN artisan down --render="errors::503" --retry=30 --no-interaction || true

echo "→ Migrations"
$PHP_BIN artisan migrate --force --no-interaction

echo "→ Lien public/storage"
$PHP_BIN artisan storage:link --force --no-interaction

echo "→ Assets Filament"
$PHP_BIN artisan filament:assets --no-interaction

echo "→ Caches (config, routes, vues, événements)"
$PHP_BIN artisan optimize:clear --no-interaction
$PHP_BIN artisan optimize --no-interaction

echo "→ Redémarrage des workers"
$PHP_BIN artisan queue:restart --no-interaction

echo "→ Fin de maintenance"
$PHP_BIN artisan up --no-interaction

echo "→ Contrôle final"
$PHP_BIN artisan mi:deploy-check || {
    echo "✗ Des points bloquants subsistent, corrigez-les avant d'annoncer l'ouverture." >&2
    exit 1
}

echo "✓ Déploiement terminé : $(date '+%d/%m/%Y %H:%M')"
