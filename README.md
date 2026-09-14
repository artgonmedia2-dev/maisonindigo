# Maison Indigo

Boutique e-commerce sur mesure de la maison marocaine de denim Maison Indigo. « Le bleu, bien coupé. »

Laravel 12 · PHP 8.3 · MySQL 8 · Redis · Inertia 2 + Vue 3 + TypeScript (SSR) · Tailwind CSS 4 · Filament 4 · Horizon · Pest.

Le contexte complet est dans [docs/CLAUDE.md](docs/CLAUDE.md), la charte dans [docs/MaisonIndigo_Charte_Graphique.html](docs/MaisonIndigo_Charte_Graphique.html) et la feuille de route dans [docs/MaisonIndigo_MVP_Roadmap.md](docs/MaisonIndigo_MVP_Roadmap.md).

## Prérequis

- PHP 8.3 avec `pdo_mysql`, `intl`, `gd`, `mbstring`, `zip`, `exif`
- Composer 2, Node 22+, MySQL 8, Redis 7

## Installation

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
# Renseigner DB_*, REDIS_*, ADMIN_EMAIL, ADMIN_PASSWORD dans .env
php artisan migrate --seed
php artisan storage:link
```

## Développement

```bash
composer dev          # artisan serve + horizon + vite en parallèle
```

Boutique sur http://localhost:8000, back-office sur http://localhost:8000/admin.

Pour le rendu côté serveur : `npm run build` puis `php artisan inertia:start-ssr`.

## Qualité

```bash
composer lint         # pint --test + larastan (niveau 6)
composer lint:fix     # pint
composer test         # pest
npm run build         # vue-tsc + vite (client et SSR)
```

## Déploiement

- **Hostinger Hébergement Premium** (mutualisé) : guide complet dans [docs/Deploiement_Hostinger.md](docs/Deploiement_Hostinger.md). Modèle d'environnement `.env.production.example`, script `deploy/hostinger/release.sh`, cron `deploy/hostinger/crontab.txt`, workflow `.github/workflows/deploy.yml` (build des assets puis rsync après CI verte).
- **VPS + Coolify** (cible du roadmap) : même code, `.env` avec Redis, Horizon et SSR activés.

## Note Windows

Horizon déclare `ext-pcntl` et `ext-posix`, absentes sur Windows. `composer.json` les déclare en `config.platform` pour que l'installation passe ; en production (Linux) les extensions réelles sont utilisées.
