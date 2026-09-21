# Déploiement sur Hostinger — Hébergement Premium

Guide pas à pas pour mettre Maison Indigo en ligne sur un hébergement mutualisé Hostinger (offre Premium). Compter une heure la première fois, cinq minutes ensuite.

## 1. Ce qui change par rapport au VPS prévu

L'hébergement mutualisé ne permet ni Redis, ni processus permanents (Horizon, serveur SSR Node). Le projet s'adapte tout seul via le `.env` :

| Sujet | VPS (Coolify) | Hostinger Premium |
|-------|---------------|-------------------|
| Cache | Redis | Fichier (`CACHE_STORE=file`) |
| Sessions | Redis | Base de données |
| File d'attente (WhatsApp, e-mails, images) | Redis + Horizon | Base de données, vidée chaque minute par le cron (`routes/console.php`) |
| Rendu côté serveur (SSR) | Activé | Désactivé (`INERTIA_SSR_ENABLED=false`) : le site reste indexable, le premier affichage est rendu par le navigateur |
| Conversions d'images | En arrière-plan | À l'envoi (`QUEUE_CONVERSIONS_BY_DEFAULT=false`) |
| HTTPS | Cloudflare / Traefik | Certificat Hostinger + `TRUSTED_PROXIES=*` et `FORCE_HTTPS=true` |
| Base de données | MySQL 8 | MariaDB (compatible, aucune migration à changer) |

Le `.htaccess` de `public/` force le https, interdit les fichiers sensibles, met les assets en cache un an et ajoute les en-têtes de sécurité.

## 2. Préparer l'hébergement (hPanel)

> **À faire en premier, sinon rien ne démarre.** Filament 4 exige **PHP 8.3**. Sur un compte Hostinger neuf, PHP est souvent en 8.2 : l'application s'arrête alors avant même d'écrire dans ses journaux, et le site renvoie une page blanche ou une erreur 500 sans explication.
>
> **Deux réglages distincts.** La version choisie dans hPanel s'applique **au site**. Le **terminal SSH** garde sa propre version, souvent 8.2. Régler le site en 8.3 ne suffit donc pas pour `php artisan`. Voir §2 bis.

1. **PHP** : hPanel → Avancé → Configuration PHP → version **8.3**. Onglet Extensions : cocher `intl`, `gd`, `exif`, `fileinfo`, `mbstring`, `zip`, `pdo_mysql`, `curl`, `sodium`. Onglet Options : `memory_limit` 256M, `upload_max_filesize` et `post_max_size` 32M, `max_execution_time` 120.
2. **Base de données** : hPanel → Bases de données → MySQL : créer la base et l'utilisateur (tout accès). Noter nom, utilisateur, mot de passe. L'hôte est `localhost`.
3. **SSH** : hPanel → Avancé → Accès SSH : activer, noter l'IP, le port (généralement `65002`) et l'utilisateur (`u123456789`). Ajouter une clé publique (celle de votre poste, et celle de GitHub Actions, voir §5).
4. **Domaine et certificat** : rattacher le domaine, activer le certificat SSL Hostinger et « Forcer HTTPS ».
5. **E-mail** : créer `bonjour@votre-domaine` dans hPanel → E-mails pour l'envoi SMTP (`smtp.hostinger.com`, port 465, SSL).

## 2 bis. PHP 8.3 dans le terminal SSH

Après avoir réglé le site en 8.3, vérifiez le terminal :

```bash
php -v
```

S'il affiche 8.2, `php artisan` refusera de démarrer avec un message vous indiquant le binaire à utiliser. Rendez la 8.3 permanente pour votre compte :

```bash
echo 'export PATH=/opt/alt/php83/usr/bin:$PATH' >> ~/.bashrc
source ~/.bashrc
php -v        # doit afficher 8.3
```

Si ce chemin n'existe pas, cherchez le vôtre :

```bash
ls /opt/alt/php83/usr/bin/php /usr/local/bin/php8* /usr/bin/php8.3 2>/dev/null
```

Le même chemin complet doit figurer dans la tâche cron (§5) : `/usr/bin/php` y est souvent en 8.2 et la tâche échouerait sans rien signaler. Le script `deploy/hostinger/release.sh` détecte le bon binaire tout seul.

## 3. Arborescence sur le serveur

Hostinger sert `~/domains/DOMAINE/public_html`. L'application doit vivre **à côté**, jamais dedans, pour que `.env`, `storage/` et `vendor/` restent inaccessibles.

```
~/domains/DOMAINE/
├── maison-indigo/        ← l'application (ce dépôt)
│   ├── public/
│   ├── storage/
│   └── .env
└── public_html → maison-indigo/public   (lien symbolique)
```

Depuis SSH :

```bash
cd ~/domains/DOMAINE
mkdir -p maison-indigo
rm -rf public_html            # vide à la création du domaine
ln -s maison-indigo/public public_html
```

Si le lien symbolique est refusé (rare), placer l'application dans `public_html/maison-indigo` et copier `deploy/hostinger/public_html.htaccess` vers `public_html/.htaccess`.

## 4. Premier déploiement

### Option A — archive prête à extraire (la plus simple, aucune configuration préalable)

Sur votre poste, une seule commande fabrique l'archive complète : dépendances de production, assets compilés, assets Filament, redirection vers `public/`, modèle d'environnement.

```bash
bash deploy/hostinger/build-package.sh
```

Elle produit `deploy-hostinger.zip` (environ 22 Mo). Ensuite, dans hPanel :

1. Gestionnaire de fichiers → `public_html` → **vider le dossier** (y compris `default.php`).
2. Téléverser `deploy-hostinger.zip`, puis clic droit → **Extraire**.
3. Renommer `.env.example` en `.env`, l'ouvrir et renseigner `APP_URL`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `ADMIN_EMAIL`, `ADMIN_PASSWORD`.
4. En SSH, dans `public_html` :

```bash
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link
php artisan db:seed --class=AdminSeeder --force
php artisan db:seed --class=SizeChartsSeeder --force
php artisan db:seed --class=ShippingZonesSeeder --force
php artisan optimize --except=views
php artisan mi:deploy-check
```

La dernière commande passe en revue PHP, les extensions, le `.env`, la base, les droits d'écriture, les assets et le contenu. Elle affiche en français ce qui bloque et comment le corriger. Tant qu'elle signale un point bloquant, le site ne fonctionnera pas correctement.

L'archive contient un `.htaccess` à sa racine qui renvoie tout vers `public/` et interdit l'accès à `.env`, `vendor/` et au reste de l'application. Si vous préférez la structure propre, réglez plutôt le dossier racine du site sur `public_html/public` (voir §9) et supprimez ce `.htaccess`.

### Option B — automatique par GitHub Actions (recommandée)

Le workflow `.github/workflows/deploy.yml` construit les assets, envoie les fichiers par `rsync` et lance `deploy/hostinger/release.sh` après chaque CI verte sur `main` (ou à la main : onglet Actions → « Déploiement Hostinger » → Run workflow).

1. Générer une paire de clés dédiée sur votre poste : `ssh-keygen -t ed25519 -C "github-actions maison-indigo" -f hostinger-deploy`. Ajouter `hostinger-deploy.pub` dans hPanel → Accès SSH → Clés.
2. Dans GitHub → Settings → Environments → créer `production`, puis y définir :

| Type | Nom | Valeur |
|------|-----|--------|
| Secret | `HOSTINGER_SSH_HOST` | IP donnée par hPanel |
| Secret | `HOSTINGER_SSH_PORT` | `65002` |
| Secret | `HOSTINGER_SSH_USER` | `u123456789` |
| Secret | `HOSTINGER_SSH_KEY` | contenu de `hostinger-deploy` (clé privée) |
| Secret | `HOSTINGER_APP_PATH` | `/home/u123456789/domains/DOMAINE/maison-indigo` |
| Variable | `APP_URL` | `https://www.DOMAINE` |

3. Sur le serveur, créer le `.env` **avant** le premier lancement :

```bash
cd ~/domains/DOMAINE/maison-indigo
cp .env.production.example .env     # après un premier rsync, ou copier le fichier à la main
nano .env                            # DB_*, ADMIN_*, CONTACT_*, MAIL_*, BANK_*, APP_URL
```

4. Lancer le workflow. Il migre la base, crée le lien `public/storage`, met les caches et vérifie `/up`.
5. Créer l'administrateur et le catalogue de départ, une seule fois :

```bash
php artisan db:seed --class=AdminSeeder --force
php artisan db:seed --class=SizeChartsSeeder --force
php artisan db:seed --class=ShippingZonesSeeder --force
php artisan db:seed --class=DemoCatalogSeeder --force     # facultatif : dix références de démonstration
```

### Option C — rsync à la main depuis votre poste

Hostinger n'a pas Node : les assets se construisent en local.

```bash
composer install --no-dev --optimize-autoloader
npm ci && npx vite build
rsync -az --delete --exclude-from=deploy/hostinger/rsync-exclude.txt \
  -e "ssh -p 65002" ./ u123456789@IP:/home/u123456789/domains/DOMAINE/maison-indigo/
ssh -p 65002 u123456789@IP "cd domains/DOMAINE/maison-indigo && bash deploy/hostinger/release.sh"
```

## 5. Le cron (obligatoire)

hPanel → Avancé → Tâches cron → Personnalisé, **toutes les minutes** (voir `deploy/hostinger/crontab.txt`) :

```
* * * * * cd /home/u123456789/domains/DOMAINE/maison-indigo && /opt/alt/php83/usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

Cette ligne suffit : le planificateur lance le worker de file d'attente (messages WhatsApp, e-mails), le ménage des jobs échoués et, plus tard, les sauvegardes.

## 6. Vérifications après mise en ligne

```bash
php artisan mi:deploy-check          # tout le contrôle en une commande
php artisan mi:deploy-check --strict # les avertissements deviennent bloquants
```

Puis, à l'œil :

- `https://www.DOMAINE/up` répond `200`.
- La home affiche les polices de la maison et l'image hero (assets servis depuis `/build`).
- `/admin` : connexion, puis Profil → activer la double authentification.
- Passer une commande test en paiement à la livraison : elle apparaît dans `/admin/orders` ; `storage/logs/laravel-*.log` trace le message WhatsApp tant que `WHATSAPP_TOKEN` est vide.
- `php artisan schedule:list` montre `queue:work database …` toutes les minutes.
- Un envoi d'image produit dans Filament crée les conversions dans `storage/app/public`.

## 6 bis. Pourquoi `--except=views`

`php artisan optimize` tout court échoue avec :

```
Unable to locate a class or view for component [filament-panels::form].
```

La page de connexion du back-office surcharge un composant de Filament dans `resources/views/vendor/`. `view:cache` compile ce fichier hors du contexte d'un panneau et ne sait pas résoudre les composants Filament qu'il utilise. C'est attendu, et sans conséquence : Blade compile ces vues à la première visite puis les conserve. Utilisez donc toujours :

```bash
php artisan optimize --except=views
```

Cette commande met bien en cache la configuration, les routes, les événements, les composants Filament et les icônes.

## 6 ter. Toutes les pages sauf l'accueil renvoient la page 404 de Hostinger

Symptôme : `/` répond, mais `/femme`, `/admin` et `/up` tombent sur « This Page Does Not Exist ». Les images et les fichiers de `build/` se chargent normalement.

Cause : la réécriture d'URL vers `index.php` ne s'applique pas. Sur certaines configurations LiteSpeed, `<IfModule mod_rewrite.c>` est ignoré en silence : les règles à l'intérieur ne s'exécutent jamais, et seule la page d'accueil répond, servie par `DirectoryIndex`.

Correction : les `.htaccess` du projet ne placent plus les règles de réécriture dans ce bloc. Renvoyez `public/.htaccess` et, si l'application est à la racine, `public_html/.htaccess`. Vérifiez ensuite :

```bash
curl -sI https://votre-domaine/up | head -1     # doit répondre 200
```

## 6 quater. Lire la cause d'une erreur 500

En production, `APP_DEBUG=false` masque le détail, ce qui est voulu. La cause est dans le journal :

```bash
php artisan mi:logs              # les cinq dernières erreurs
php artisan mi:logs --full       # avec la trace complète
```

Ne repassez jamais `APP_DEBUG=true` sur un site ouvert au public : la page d'erreur affiche alors vos identifiants de base de données.

## 6 quinquies. Les photos produit ne s'affichent pas

Quatre causes, dans cet ordre.

**Les photos sont écrites hors du web.** Sans disque imposé, Filament suit
`FILESYSTEM_DISK`. Avec `FILESYSTEM_DISK=local`, les images partent dans
`storage/app/private`, que le serveur ne sert pas : `/storage/…` renvoie 404
alors que le fichier existe. C'est la cause la plus trompeuse, car tout paraît
correct — enregistrement en base, fichier sur le disque, droits en ordre.

```bash
find storage/app -type f ! -name '.gitignore' | head   # tout doit être sous app/public
php artisan mi:media-disk --force                      # rapatrie l'existant
php artisan media-library:regenerate --force
```

Ajoutez `FILAMENT_FILESYSTEM_DISK=public` à votre `.env` pour que cela ne
recommence pas.

**Le lien `public/storage` manque.** Les archives ZIP extraites par le gestionnaire de fichiers ne conservent pas les liens symboliques.

```bash
ls -la public/storage
rm -rf public/storage && php artisan storage:link
```

**Les vignettes attendent un worker.** Media Library met les conversions en file d'attente par défaut. Sur mutualisé, sans worker permanent, elles ne sont jamais générées : l'adresse `/storage/<id>/conversions/…` renvoie alors 404, et la fiche produit affiche une image cassée.

```bash
grep QUEUE_CONVERSIONS .env          # doit valoir false
php artisan config:clear
php artisan queue:work --stop-when-empty    # traite ce qui attend déjà
php artisan media-library:regenerate        # régénère l'existant
php artisan optimize --except=views
```

**Les fichiers ne sont plus sur le serveur.** C'est le cas le plus sévère : la base
garde les enregistrements, le disque est vide, et toutes les adresses `/storage/…`
renvoient 404, y compris les originaux.

```bash
ls -la storage/app/public/     # ne contient que .gitignore : les photos ont disparu
php artisan mi:media-prune     # remet la base en accord avec le disque
```

Il ne reste plus qu'à reverser les photos depuis le back-office. Pour éviter que cela
se reproduise, voir l'encadré ci-dessous.

`php artisan mi:deploy-check` signale désormais les trois cas : originaux manquants
(bloquant), vignettes fantômes et conversions en file d'attente (avertissements).

> **Les photos ne sont pas dans le dépôt.** `storage/app/public/` vit uniquement sur
> le serveur : ni Git, ni l'archive ZIP, ni `rsync` ne le reconstituent. Avant toute
> remise en ligne qui réécrit `public_html`, sauvegardez-le, et restaurez-le après :
>
> ```bash
> tar czf ~/medias-$(date +%F).tar.gz -C ~/public_html storage/app/public
> # … remise en ligne …
> tar xzf ~/medias-AAAA-MM-JJ.tar.gz -C ~/public_html
> php artisan storage:link
> ```

## 7. Exploitation

| Besoin | Commande (SSH, dans `maison-indigo/`) |
|--------|----------------------------------------|
| Redéployer | relancer le workflow, ou `bash deploy/hostinger/release.sh` |
| Journaux | `php artisan mi:logs` |
| Jobs échoués | `php artisan queue:failed`, `php artisan queue:retry all` |
| Vider les caches | `php artisan optimize:clear && php artisan optimize --except=views` |
| Maintenance | `php artisan down --render="errors::503"` puis `php artisan up` |
| Sauvegarde base | hPanel → Fichiers → Sauvegardes (quotidiennes, incluses dans l'offre), ou `mysqldump` |

## 8. Limites connues et moment de migrer vers un VPS

- Pas de SSR : le rendu initial dépend du navigateur ; Google indexe les pages, mais le score Lighthouse « performance » sera un peu plus bas que sur VPS.
- Le worker tourne au plus 55 secondes par minute : un message WhatsApp peut partir jusqu'à une minute après la commande.
- Les webhooks WhatsApp (sprint 4) et les pixels serveur (sprint 5) fonctionnent, mais surveillez les limites de processus PHP de l'offre.
- Quand les commandes dépasseront quelques dizaines par jour, ou dès que le SSR et Horizon deviennent nécessaires, reprendre le plan Coolify + VPS de `docs/MaisonIndigo_MVP_Roadmap.md` : seul le `.env` change.

## 9. Dépannage : « j'ai tout envoyé dans public_html et le site affiche la page Hostinger »

Symptôme : `public_html` contient `app/`, `vendor/`, `.env`, `public/`… et le navigateur montre « Vous êtes prêt à partir ! » (le fichier `default.php` de Hostinger). Cause : Hostinger cherche `index.php` à la racine de `public_html`, or celui de Laravel est dans `public/`. En plus, `.env` et `vendor/` sont exposés sur Internet.

### Solution la plus simple : changer le dossier racine du site (hPanel, sans terminal)

hPanel → Sites web → le site → Tableau de bord → **Avancé** → **Changer le dossier racine du site**, puis saisir :

```
public_html/public
```

Le serveur sert alors directement `public/`, le `.htaccess` de Laravel s'applique, et tout le reste de l'application (`.env`, `vendor/`, `storage/`) sort du dossier public : inaccessible depuis Internet. Aucun fichier à créer.

### Remise en ordre propre (SSH, 5 minutes)

```bash
cd ~/domains/DOMAINE
mkdir maison-indigo
# tout déplacer, fichiers cachés compris, sauf le placeholder Hostinger
shopt -s dotglob && mv public_html/* maison-indigo/ && shopt -u dotglob
rm -f maison-indigo/default.php
rm -rf maison-indigo/node_modules maison-indigo/.git maison-indigo/tests   # inutiles en production
rmdir public_html && ln -s maison-indigo/public public_html
cd maison-indigo
cp .env.production.example .env && nano .env      # DB_*, ADMIN_*, CONTACT_*, MAIL_*, APP_URL
php artisan key:generate --force
bash deploy/hostinger/release.sh
php artisan db:seed --class=AdminSeeder --force
php artisan db:seed --class=SizeChartsSeeder --force
php artisan db:seed --class=ShippingZonesSeeder --force
```

### Repli sans SSH (Gestionnaire de fichiers hPanel)

1. Supprimer `public_html/default.php`.
2. Copier le contenu de `deploy/hostinger/public_html-racine.htaccess` dans un nouveau fichier `public_html/.htaccess`.
3. Réécrire `public_html/.env` à partir de `.env.production.example` (base MySQL, `APP_URL`, comptes). Sans SSH, `APP_KEY` se génère en local : `php artisan key:generate --show`, puis coller la valeur.
4. Les migrations et le lien `storage` exigent tout de même un passage par SSH (`php artisan migrate --force`, `php artisan storage:link`). Hostinger Premium inclut l'accès SSH : hPanel → Avancé → Accès SSH.

Vérifier ensuite `https://DOMAINE/up` (doit répondre 200) et `https://DOMAINE/.env` (doit répondre 403 ou 404, jamais le contenu).
