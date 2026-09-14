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
PHP_BIN="${PHP_BIN:-php}"

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

echo "✓ Déploiement terminé : $(date '+%d/%m/%Y %H:%M')"
