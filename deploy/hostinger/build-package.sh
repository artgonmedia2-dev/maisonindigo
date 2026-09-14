#!/usr/bin/env bash
# ------------------------------------------------------------------
# Maison Indigo — fabrique l'archive à envoyer sur Hostinger.
#
# Produit `deploy-hostinger.zip` : le contenu s'extrait directement dans
# public_html et le site fonctionne. Sont exclus le dépôt git, les tests,
# node_modules, les outils de développement et tout fichier d'environnement.
#
#   bash deploy/hostinger/build-package.sh
#
# Prérequis sur votre poste : php 8.3, composer, node.
# ------------------------------------------------------------------
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
STAGING="$ROOT/.deploy-build"
ARCHIVE="$ROOT/deploy-hostinger.zip"

PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"

cd "$ROOT"

echo "→ Vérification des outils"
for tool in "$PHP_BIN" "$COMPOSER_BIN" npm; do
    command -v "$tool" >/dev/null 2>&1 || { echo "✗ $tool introuvable." >&2; exit 1; }
done

PHP_OK=$("$PHP_BIN" -r 'echo PHP_VERSION_ID >= 80300 ? 1 : 0;')
if [[ "$PHP_OK" != "1" ]]; then
    echo "✗ PHP 8.3 requis sur votre poste ($("$PHP_BIN" -r 'echo PHP_VERSION;'))." >&2
    exit 1
fi

echo "→ Compilation des assets (client uniquement, pas de rendu serveur)"
npm install --no-audit --no-fund --silent
npm run build:client

echo "→ Préparation du dossier de construction"
rm -rf "$STAGING" "$ARCHIVE"
mkdir -p "$STAGING"

# Le code, sans dépendances ni fichiers de développement
tar --exclude-vcs \
    --exclude='./vendor' \
    --exclude='./node_modules' \
    --exclude='./tests' \
    --exclude='./docs' \
    --exclude='./.deploy-build' \
    --exclude='./.github' \
    --exclude='./.env' \
    --exclude='./.env.backup' \
    --exclude='./.env.production' \
    --exclude='./storage/app/public/*' \
    --exclude='./storage/framework/cache/data/*' \
    --exclude='./storage/framework/sessions/*' \
    --exclude='./storage/framework/views/*' \
    --exclude='./storage/logs/*' \
    --exclude='./bootstrap/cache/*' \
    --exclude='./bootstrap/ssr' \
    --exclude='./public/hot' \
    --exclude='./public/storage' \
    --exclude='./database/database.sqlite' \
    --exclude='./deploy-hostinger.zip' \
    --exclude='./phpunit.xml' \
    --exclude='./phpstan.neon' \
    --exclude='./pint.json' \
    --exclude='./response.html' \
    -cf - . | (cd "$STAGING" && tar -xf -)

echo "→ Dépendances PHP de production"
(cd "$STAGING" && "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-progress --quiet)

echo "→ Assets Filament"
(cd "$STAGING" && cp "$ROOT/.env.production.example" .env.tmp \
    && "$PHP_BIN" -r '$e=file_get_contents(".env.tmp"); file_put_contents(".env", str_replace("APP_KEY=", "APP_KEY=base64:".base64_encode(random_bytes(32)), $e));' \
    && "$PHP_BIN" artisan filament:assets --no-interaction --quiet \
    && rm -f .env .env.tmp)

echo "→ Arborescence de stockage"
mkdir -p "$STAGING"/storage/app/public \
         "$STAGING"/storage/framework/{cache/data,sessions,views} \
         "$STAGING"/storage/logs \
         "$STAGING"/bootstrap/cache
touch "$STAGING"/storage/logs/.gitkeep

echo "→ Redirection vers public/"
cp "$ROOT/deploy/hostinger/public_html-racine.htaccess" "$STAGING/.htaccess"

echo "→ Modèle d'environnement"
cp "$ROOT/.env.production.example" "$STAGING/.env.example"

echo "→ Archive"
if command -v zip >/dev/null 2>&1; then
    (cd "$STAGING" && zip -rqX "$ARCHIVE" . -x '*.DS_Store')
else
    # Git Bash sous Windows n'a pas « zip » : on passe par l'extension zip de PHP.
    "$PHP_BIN" "$ROOT/deploy/hostinger/zip.php" "$STAGING" "$ARCHIVE"
fi
rm -rf "$STAGING"

SIZE=$(du -h "$ARCHIVE" | cut -f1)
echo
echo "✓ Archive prête : deploy-hostinger.zip ($SIZE)"
echo
echo "  1. hPanel → Gestionnaire de fichiers → public_html (le vider d'abord)"
echo "  2. Téléverser deploy-hostinger.zip, puis « Extraire »"
echo "  3. Renommer .env.example en .env et le renseigner (base, APP_URL, admin)"
echo "  4. En SSH, dans public_html :"
echo "       php artisan key:generate --force"
echo "       php artisan migrate --force"
echo "       php artisan storage:link"
echo "       php artisan db:seed --class=AdminSeeder --force"
echo "       php artisan optimize"
echo "       php artisan mi:deploy-check"
