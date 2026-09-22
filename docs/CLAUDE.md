# CLAUDE.md — MAISON INDIGO

Contexte projet pour Claude Code. Lis ce fichier en entier avant toute tâche. Les décisions listées ici sont prises : ne les remets pas en question et ne pose pas de question dont la réponse est ici.

## Le projet

Maison Indigo est une marque marocaine de jeans premium vendue en ligne (399–599 dh le jean). Cible 25–40 ans, urbains, CSP+, paiement à la livraison majoritaire. Promesse : « Le bleu, bien coupé. » — un denim bien fait, dans la bonne coupe et la bonne taille. Arguments : qualité du denim (12–14 oz), profondeur de tailles (26–42, 3 longueurs), guide des tailles fiable, service (échange offert, livraison en boîte). Esthétique éditoriale et sobre, jamais promotionnelle.

Documents de référence dans `docs/` :
- `MaisonIndigo_Brief_Projet.md` — positionnement, catalogue, offres, plan de lancement
- `MaisonIndigo_Charte_Graphique.html` — tokens visuels, composants, voix. Source de vérité du design.
- `MaisonIndigo_MVP_Roadmap.md` — périmètre MVP, architecture, modèle de données, sprints

## Stack (décidé, sur mesure — pas de Shopify)

- **Laravel 12**, PHP 8.3, MySQL 8, Redis (cache, sessions, queues via **Horizon**)
- **Inertia.js 2 + Vue 3 + TypeScript**, **SSR activé**, **Tailwind CSS 4** (`@theme` dans `app.css`), Vite. Composition API `<script setup lang="ts">` uniquement.
- **Filament 4** pour le back-office `/admin`
- **Laravel Breeze** (stack Inertia Vue TS) pour l'auth clients ; Filament a ses propres users (`admins`)
- **Spatie Media Library** (conversions WebP + AVIF, responsive images), **Spatie Laravel Settings** pour `settings`
- **Pest** pour les tests, **Pint** (preset laravel), **Larastan** niveau 6
- Paiement MVP : COD + virement. **Aucune intégration carte** avant la phase 2.
- WhatsApp : Meta Cloud API appelée depuis `App\Services\WhatsAppService`, envois toujours en job
- E-mail : Laravel Mail (Brevo en prod, `log` en local). Membres : MailerLite API.
- Déploiement : Coolify sur VPS, Cloudflare devant. Pas de Docker en local (Herd/Valet ou `artisan serve`).
- Langue : français uniquement. Tous les textes UI passent par `lang/fr/*.php` ou `resources/js/i18n/fr.ts` — jamais en dur dans les composants.

## Structure

Voir `docs/MaisonIndigo_MVP_Roadmap.md` §3. Résumé des règles :
- Logique métier dans `app/Actions/{Domaine}/{Verbe}{Nom}.php` (une classe, une méthode `handle`). Les contrôleurs orchestrent, ils ne calculent pas.
- Services stateless dans `app/Services/`. Enums PHP 8.3 backed dans `app/Enums/`.
- Contrôleurs storefront dans `App\Http\Controllers\Storefront`, webhooks dans `Webhooks`.
- Composants Vue de la maison préfixés **`Mi`** dans `resources/js/Components/mi/`. Pages dans `resources/js/Pages/`.
- Props Inertia typées : un type TS par page dans `resources/js/types/`.
- Prix en **centimes entiers** (int) en base et dans les props. Formatage uniquement via `App\Support\Money` côté PHP et `useMoney()` côté Vue : « 499,00 dh ».
- Pas de N+1 : `with()` explicite, `preventLazyLoading()` activé en local et tests.

## Tokens de design (source : la charte)

```css
@theme {
  --color-mi-indigo: #1B2A4A;      /* logo, titres, bouton principal */
  --color-mi-indigo-deep: #0F1B33; /* indigo nuit : fonds premium, hover */
  --color-mi-stone: #4F6D9A;       /* liens, badges secondaires */
  --color-mi-ecru: #F4EFE6;        /* fond de page */
  --color-mi-fil: #8A8F98;         /* texte secondaire */
  --color-mi-charbon: #141414;     /* texte */
  --color-mi-ocre: #D9822B;        /* surpiqûre, détails, éditions limitées — max 10 % d'un écran */
  --color-mi-vert: #2E7D5B;        /* stock, succès */
  --font-display: "Cormorant Garamond", Georgia, serif;
  --font-body: "Inter", system-ui, sans-serif;
}
```

Polices : Cormorant Garamond 500/600 (titres, logo, prix mis en avant), Inter 400/500/600 (corps), auto-hébergées dans `public/fonts` avec `font-display: swap`. Échelle : H1 44/48 · H2 32/36 · H3 22/26 · body 16/24 · small 13/18. Titres serif en bas de casse avec majuscule initiale.

Règles visuelles :
- `border-radius: 0` partout, sauf badges « patch » découpés en biais via `clip-path` (coins coupés 6 px)
- Séparateurs = trait pointillé ocre 2 px, avec parcimonie
- Un seul bouton indigo plein par écran ; les autres en outline indigo ou ghost stone
- Mise en page aérée : marges généreuses, grilles 2 colonnes mobile / 3 desktop (jamais 4)
- Photos produit ratio 4:5, 6 vues, fond écru
- Une taille en rupture reste **visible**, barrée, bordure pointillée, avec « Me prévenir ». Jamais masquée.
- Pas de capitales sur les titres courants. Capitales espacées réservées au logo et aux petits libellés (badges, « Maison »).
- Aucun badge promo, prix barré ou compte à rebours en dehors du gabarit « vente privée ». Badges autorisés : Nouveau, Édition limitée, Atelier.
- Pas d'animations décoratives. Transitions uniquement sur les actions utilisateur, ≤ 200 ms, `prefers-reduced-motion` respecté.
- CSS logique (`inline-start/end`, `ms-`/`me-` Tailwind) pour préparer le RTL. Jamais `left/right` pour du layout.
- Focus visible : `outline: 2px solid var(--color-mi-indigo); outline-offset: 3px`.

## Voix (pour tout texte : lang, seeders de pages, e-mails, messages WhatsApp, Filament)

- **Vouvoiement** partout dans le code. (Le tutoiement n'existe qu'en légende réseaux, hors périmètre.)
- Phrases courtes, pas de point d'exclamation
- Précis : tissu, grammage, origine du denim, taille du modèle, délai, prix
- Ton d'un tailleur moderne : assuré, chaleureux, jamais vendeur. Pas d'« offre exceptionnelle », pas d'urgence artificielle
- Exemples : « Ajouter au panier » · « Trouver ma taille » · « Entre deux tailles, choisissez la plus petite. » · « Nous vous envoyons la 34 demain ; vous remettez la 32 au livreur. » · « Votre bleu est arrivé. »

## Modèle de données

Détail complet dans le roadmap §3. Points non négociables :
- **Produit** = 1 coupe × 1 lavage × 1 genre. Le titre est pré-rempli avec `{Coupe} {Lavage}` (« Straight Indigo Brut ») et reste modifiable dans le back-office ; il cesse de suivre la composition dès qu'il est réécrit. Genre en champ, jamais dans le titre. Jamais « Maison Indigo » dans le titre produit.
- **Coupes et lavages** vivent en base (`cuts`, `washes`), gérables depuis le back-office : nom, identifiant court, code SKU de trois lettres, genres concernés, ordre, mise en vente. Les produits stockent l'identifiant court, qui voyage dans les adresses et les SKU : il se verrouille dès qu'un produit l'emploie, et un terme employé ne se supprime pas — il se retire de la vente.
- **Variante** = taille (26–42) × longueur (30/32/34), SKU `MI-{genre}-{coupe}-{lavage}-{taille}-{longueur}` (ex. `MI-H-STR-BRU-32-32`), stock entier.
- Enums : `Gender` (homme, femme), `OrderStatus` (new, confirmed, to_callback, prepared, shipped, delivered, cancelled, returned), `PaymentMethod` (cod, transfer). Coupes et lavages ne sont plus des enums : voir la ligne précédente. Vocabulaire livré à la migration — coupes homme : baggy, straight, regular, slim, relaxed, tapered ; coupes femme : baggy, wide_leg, straight, mom, slim, bootcut, flare ; lavages : brut, stone, clair, noir, gris, ecru.
- Numéro de commande `MI-{AAAA}-{NNNNNN}` séquentiel par année, généré dans une transaction.
- Transitions de statut uniquement via `Actions/Orders/TransitionOrderStatus` qui valide la transition et écrit l'historique.
- Stock décrémenté à la création de commande, restitué à l'annulation. Pas de réservation panier au MVP.
- Adresse de livraison **snapshottée** en JSON sur la commande, prix des lignes snapshottés.

## Commandes

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed            # inclut DemoCatalogSeeder + admin
php artisan serve                     # + npm run dev (ou composer dev si script défini)
php artisan horizon
composer test                         # pest
composer lint                         # pint --test + larastan
php artisan mi:import-catalog data/catalog.xlsx
php artisan mi:check-images
```

Variables d'environnement (`.env.example`, jamais de valeur réelle committée) : `APP_URL`, `DB_*`, `REDIS_*`, `MAIL_*`, `WHATSAPP_TOKEN`, `WHATSAPP_PHONE_ID`, `WHATSAPP_VERIFY_TOKEN`, `WHATSAPP_APP_SECRET`, `MAILERLITE_TOKEN`, `META_PIXEL_ID`, `META_CAPI_TOKEN`, `TIKTOK_PIXEL_ID`, `GA4_ID`, `AWS_*` (médias/sauvegardes).

## Conventions de travail

- **Avant de coder** : lire la section du roadmap correspondant au sprint, lister les fichiers à créer/modifier, puis exécuter. Ne pas demander confirmation pour des choix tranchés ici. Si un vrai blocage apparaît, proposer une option par défaut et continuer.
- **Tests d'abord pour le métier** : chaque Action et Service a un test Pest. Feature tests sur les parcours (collection, produit, panier, checkout, webhook). Pas de merge sans `composer test` vert.
- **Migrations** : jamais modifier une migration déjà committée ; en créer une nouvelle. Clés étrangères et index déclarés.
- **Commits** : un par fonctionnalité, message en français, préfixe `feat:`, `fix:`, `test:`, `chore:`, `docs:`. Ex. `feat: sélecteur taille × longueur avec rupture visible`.
- **Qualité** : `composer lint` propre avant chaque commit. Pas de `any` en TS. Pas de dépendance npm > 30 Ko sans justification écrite dans le PR.
- **Performance** : Lighthouse mobile ≥ 90. Images via Media Library `srcset` AVIF/WebP, `loading="lazy"` sauf hero (`fetchpriority="high"`). Collections cachées en Redis, invalidées à la sauvegarde produit.
- **Sécurité** : validation par FormRequest, rate limiting sur checkout / contact / webhooks, signature vérifiée sur le webhook Meta, 2FA sur Filament, aucun secret dans le code ou les fixtures.
- **Fin de tâche** : résumer ce qui a été fait, ce que Youssef doit configurer (comptes, .env, saisie Filament), et ce qu'il doit vérifier visuellement. Une liste, pas un rapport.

## SEO / GEO

Structure complète dans `docs/MaisonIndigo_SEO_Baggy_Homme.md`. Règles en vigueur :
- Un hub par coupe et par genre : `/{genre}/jean-{coupe}`. Un seul hub par terme, jamais de page jumelle.
- Sous-collections par lavage : `/{genre}/jean-{coupe}/{lavage}`, limitées à quatre lavages indexables (noir, bleu, brut, clair). Toute autre valeur renvoie 404.
- Facettes taille, longueur, prix et tri en paramètres d'URL : `noindex, follow` et canonical vers l'URL propre.
- Produits sur `/{genre}/{slug}`, Journal sur `/journal/{slug}`, auteurs sur `/journal/auteur/{slug}`.
- Titles, meta et contenu enrichi éditables dans Filament, jamais en dur.
- JSON-LD rendu côté serveur dans le gabarit Blade, pas dans Vue : le SSR est désactivé en production.
- Entité de marque, formulation unique partout : « Maison Indigo, marque marocaine de jeans premium basée à Nador ».

## Hors périmètre MVP — ne pas implémenter sans demande explicite

Paiement carte / CMI, arabe / RTL, avis clients, fidélité / parrainage, panier abandonné, recherche Meilisearch, Google Shopping, multi-devise, collection Enfant, app mobile, Docker local.
