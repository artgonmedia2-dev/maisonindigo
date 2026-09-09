# MAISON INDIGO — MVP & feuille de route développement

Version 3.0 · Septembre 2026 · ARTGON MEDIA
Boutique développée **sur mesure** (pas de Shopify). À lire avec `CLAUDE.md` et `docs/MaisonIndigo_Charte_Graphique.html`.

---

## 1. Stack (décidé)

| Couche | Choix | Pourquoi |
|--------|-------|----------|
| Backend | Laravel 12 · PHP 8.3 · MySQL 8 · Redis | Maîtrisé par l'agence (RifLiving), écosystème complet |
| Front | Inertia.js 2 + Vue 3 + TypeScript + Tailwind CSS 4 + Vite, SSR activé | Une seule app, pas d'API à maintenir, SEO via SSR |
| Back-office | Filament 4 | Admin complet en quelques jours : catalogue, commandes, stocks, COD |
| Médias | Spatie Media Library + conversions WebP/AVIF | Galeries produit propres, responsive |
| Files d'attente | Laravel Horizon (Redis) | WhatsApp, e-mails, images en arrière-plan |
| Paiement | COD + virement (MVP) · CMI page hébergée (phase 2) | Réalité du marché marocain |
| WhatsApp | Meta Cloud API depuis Laravel | Confirmation COD sans outil tiers |
| E-mail | Laravel Mail (Brevo/Resend) · MailerLite pour les membres | Transactionnel fiable, marketing séparé |
| Tests / qualité | Pest · Pint · Larastan niveau 6 · GitHub Actions | Non négociable sur du sur-mesure |
| Hébergement | VPS Hetzner/OVH · Coolify ou Forge · Cloudflare · sauvegardes S3 quotidiennes | < 400 dh/mois, contrôle total |

Ce que Claude Code développe : tout le code (migrations, modèles, Filament, pages Inertia/Vue, jobs, tests, scripts d'import, config déploiement).
Ce que vous faites : comptes (Meta WhatsApp, Brevo, MailerLite, VPS, domaine), saisie du catalogue dans Filament, validation visuelle.

---

## 2. Périmètre MVP

### Inclus

| Bloc | Détail |
|------|--------|
| Catalogue | Homme + Femme, ~43 refs, variantes taille × longueur, filtres coupe / lavage / taille / prix, tri, pagination |
| Fiche produit | 6 photos avec zoom, sélecteur taille × longueur avec stock visible (rupture barrée + « Me prévenir »), infos matière, tableau des mesures, badges patch, « Complète le look » |
| Trouve ma taille | Quiz 4 étapes → coupe + taille recommandées + 3 produits |
| Panier | Panier tiroir, persistant (session invité / compte), barre livraison offerte 600 dh, réduction automatique 2 jeans -10 % |
| Checkout | Une page, invité par défaut, compte optionnel. Adresse, zone → frais calculés, COD ou virement, récapitulatif |
| Commandes | Statuts : nouvelle → confirmée → préparée → expédiée → livrée / annulée / retournée. Historique, notes internes |
| Confirmation COD | Job WhatsApp à la création : « Répondez 1 pour confirmer » → webhook → statut confirmée / à rappeler |
| Back-office Filament | Produits, variantes, stocks, médias, collections, tableaux de mesures, commandes (kanban statuts), clients, zones de livraison, codes promo, pages, membres, alertes stock |
| Comptes clients | Inscription/connexion (Breeze Inertia), historique commandes, adresses, « Me prévenir » |
| Pages CMS | Accueil, La maison, Guide des tailles, Entretien, FAQ, Contact, CGV, Retours, Confidentialité — éditables dans Filament |
| E-mails | Confirmation commande, expédition, retour en stock, bienvenue membre |
| SEO | Meta par gabarit, JSON-LD Product/Breadcrumb/Organization, sitemap, canonical, SSR |
| Tracking | Meta Pixel + CAPI (server-side), TikTok Pixel, GA4, bandeau consentement |
| Perf | Lighthouse mobile ≥ 90, LCP < 2 s, images responsive AVIF/WebP |
| Langue | FR uniquement (i18n prête) |

### Exclus (phase 2)

CMI/carte · Arabe RTL · Avis clients · Fidélité/parrainage · Panier abandonné WhatsApp · Recherche Meilisearch · Google Shopping · Multi-devise diaspora · Enfant · App mobile

---

## 3. Architecture

```
maison-indigo/
├── CLAUDE.md
├── README.md
├── .env.example
├── .github/workflows/ci.yml          ← pint, larastan, pest, build vite
├── app/
│   ├── Actions/                      ← logique métier : Cart/, Checkout/, Orders/, Catalog/, SizeQuiz/
│   ├── Enums/                        ← OrderStatus, PaymentMethod, Gender, Cut, Wash
│   ├── Filament/                     ← Resources/, Pages/, Widgets/ (Filament 4)
│   ├── Http/
│   │   ├── Controllers/Storefront/   ← Home, Collection, Product, Cart, Checkout, Account, Page, SizeQuiz
│   │   ├── Controllers/Webhooks/     ← WhatsAppController
│   │   └── Middleware/               ← HandleInertiaRequests (cart count, menus, settings)
│   ├── Jobs/                         ← SendWhatsAppMessage, NotifyBackInStock, SendMetaCapiEvent
│   ├── Models/
│   ├── Notifications/                ← OrderConfirmed, OrderShipped, BackInStock, MemberWelcome
│   ├── Services/                     ← WhatsAppService, ShippingCalculator, DiscountEngine, MailerLiteService, SeoService
│   └── Support/                      ← Money (centimes, format « 499,00 dh »)
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/                      ← DemoCatalogSeeder (10 produits), ShippingZonesSeeder, SizeChartsSeeder, PagesSeeder
├── resources/
│   ├── css/app.css                   ← @theme Tailwind 4 avec tokens --mi-*
│   ├── js/
│   │   ├── app.ts · ssr.ts
│   │   ├── Layouts/StorefrontLayout.vue · CheckoutLayout.vue · AccountLayout.vue
│   │   ├── Pages/                    ← Home, Collection/Show, Product/Show, Cart, Checkout/*, Account/*, Page/Show, SizeQuiz
│   │   ├── Components/mi/            ← MiLogo, MiButton, MiPatch, MiPrice, MiProductCard, MiSizeSelector, MiSizeChart, MiGallery, MiTrustBar, MiCartDrawer
│   │   └── composables/              ← useCart, useMoney, useSizeQuiz, useTracking
│   └── views/app.blade.php
├── routes/web.php · routes/webhooks.php
├── scripts/
│   ├── import-catalog.php            ← artisan mi:import-catalog data/catalog.xlsx
│   └── check-images.php              ← artisan mi:check-images
├── data/catalog.xlsx · size-charts.json
├── docs/                             ← brief, charte, roadmap
└── tests/Feature · tests/Unit        ← Pest
```

### Modèle de données

| Table | Champs clés |
|-------|-------------|
| `products` | slug, title (« Straight Indigo Brut »), gender (enum), cut (enum), wash (enum), description, price (int centimes), compare_at_price (nullable, ventes privées), fabric_origin, weight_oz, composition, model_height_cm, model_size, size_advice, size_chart_id, status (draft/active/archived), is_new, is_featured, is_atelier, meta_title, meta_description |
| `product_variants` | product_id, size (26–42), length (30/32/34), sku, stock, low_stock_threshold, position |
| `product_relations` | product_id, related_id, type (complete_look) |
| `media` (Spatie) | 6 vues ordonnées : face, dos, profil, tissu, détail, porté |
| `collections` | slug, title, description, type (manual/rule), rules JSON, position, media |
| `collection_product` | pivot |
| `size_charts` / `size_chart_rows` | gender, cut · size, waist_cm, hips_cm, thigh_cm, inseam_30/32/34 |
| `customers` | Laravel users : name, email, phone (E.164), password nullable (invité) |
| `addresses` | customer_id, name, phone, line1, line2, city, region, zone_id, is_default |
| `shipping_zones` / `shipping_rates` | nom, villes JSON, délai · seuil offert, prix (int) |
| `carts` / `cart_items` | session_id ou customer_id · variant_id, qty, unit_price snapshot |
| `orders` | number (MI-2026-000123), customer_id, status (enum), payment_method (cod/transfer), subtotal, discount_total, shipping_total, total, currency MAD, shipping_address JSON, notes, confirmed_at, shipped_at, delivered_at, whatsapp_status |
| `order_items` | order_id, variant_id, title snapshot, size, length, sku, qty, unit_price, total |
| `order_status_histories` | order_id, from, to, user_id, comment |
| `discounts` | code (nullable = automatique), type (percent/fixed/bundle), value, rules JSON, starts_at, ends_at, usage_limit, active |
| `stock_alerts` | variant_id, email/phone, notified_at |
| `members` | email, phone, source, mailerlite_id, consent_at |
| `pages` | slug, title, blocks JSON (éditeur Filament), meta |
| `settings` | clé/valeur (seuil livraison, WhatsApp, pixels, textes légaux) |
| `whatsapp_messages` | order_id, direction, payload, status |

Prix en **centimes entiers** partout ; formatage « 499,00 dh » via `Money`.

---

## 4. Feuille de route — 7 sprints d'une semaine

Chaque sprint : Claude Code développe avec tests, vous validez sur `php artisan serve` + `npm run dev`, puis merge sur `main`. Déploiement staging dès le sprint 2.

### Sprint 0 — Fondations (3 jours)

- Laravel 12 + Breeze (Inertia Vue TS + SSR) + Tailwind 4 + Filament 4 + Horizon + Spatie Media Library + Pest + Pint + Larastan
- Tokens `--mi-*` dans `app.css` via `@theme`, polices Cormorant Garamond / Inter, règles globales (radius 0, focus, reduced-motion, logical properties)
- `StorefrontLayout.vue` : header (logo SVG deux lignes, nav Femme / Homme / Nouveautés / Atelier / Trouver ma taille, recherche, compte, panier), footer, `MiTrustBar`
- Composants de base : `MiLogo`, `MiButton` (primary/outline/ghost), `MiPatch`, `MiPrice`
- Enums, migrations et modèles de **tout** le modèle de données (vides mais typés), factories
- Filament installé avec un utilisateur admin, thème aux couleurs de la maison
- CI GitHub Actions, `.env.example`, README

**Fait quand** : home vide avec header/footer aux bonnes polices, `/admin` accessible, `composer test` vert, CI verte.

### Sprint 1 — Catalogue & back-office produits

- Filament Resources : Product (variantes en relation manager, médias ordonnés, SEO), Collection, SizeChart, ShippingZone, Setting
- `DemoCatalogSeeder` : 10 produits réalistes avec images placeholder, tous lavages et genres
- `artisan mi:import-catalog` depuis `data/catalog.xlsx` (1 ligne par variante) + `mi:check-images`
- Storefront : `Collection/Show.vue` avec filtres (coupe, lavage, taille dispo, prix), tri, pagination, URL partageable ; `MiProductCard` (4:5, badge, coupe + lavage, sous-titre, prix tabulaire)
- Accueil : hero éditorial, tuiles Femme / Homme, nouveautés, bloc matière/atelier, bloc quiz

**Fait quand** : vous saisissez un produit complet dans Filament et il apparaît filtrable sur mobile.

### Sprint 2 — Fiche produit & panier

- `Product/Show.vue` : `MiGallery` (6 vues, zoom, swipe mobile), `MiSizeSelector` (grille taille × longueur, rupture visible barrée, « Me prévenir » → `stock_alerts`), bloc matière (origine, grammage, composition, taille du modèle, conseil), `MiSizeChart` depuis `size_charts`, « Complète le look », bloc livraison par zone, JSON-LD Product
- Panier : `Actions/Cart/*`, `useCart`, `MiCartDrawer`, persistance invité (session) → compte (fusion à la connexion), barre livraison offerte, `DiscountEngine` avec règle bundle 2 jeans -10 %
- Déploiement staging (Coolify) avec base de démo

**Fait quand** : parcours collection → produit → panier fonctionnel sur staging, stock décrémenté à la réservation, tests Pest sur Cart et DiscountEngine.

### Sprint 3 — Checkout, commandes, quiz

- `Checkout/*` : une page, invité ou compte, adresse + zone → `ShippingCalculator`, choix COD / virement (instructions RIB), récap, CGV, création commande transactionnelle, décrément stock, numéro `MI-2026-000001`
- Notifications : confirmation commande (e-mail), instructions virement
- Page « Merci » avec suivi, `Account/Orders` pour les clients connectés
- `SizeQuiz.vue` + `useSizeQuiz` : 4 étapes, logique dans `data/size-charts.json`, résultat coupe + taille + 3 produits, sans rechargement
- Filament : OrderResource avec vue kanban par statut, transitions contrôlées, historique, impression bon de préparation

**Fait quand** : 10 commandes test passées sur staging (COD + virement), visibles et traitables dans Filament, tests Pest sur Checkout et OrderStatus.

### Sprint 4 — WhatsApp COD, stocks, remises

- `WhatsAppService` (Meta Cloud API) + `SendWhatsAppMessage` job : à la création d'une commande COD → template « Bonjour {prénom}, votre commande Maison Indigo n° {n} : {articles}. Répondez 1 pour confirmer, 2 pour modifier. »
- `Webhooks/WhatsAppController` : réception réponse → statut `confirmée` / `à rappeler`, notification admin (e-mail + badge Filament)
- Messages expédition (avec transporteur + délai) et retour en stock (`NotifyBackInStock`)
- Alertes stock bas dans Filament (widget + e-mail quotidien)
- DiscountResource : codes (INDIGO10, usage unique par client), remise automatique bundle, gabarit « vente privée » (dates, compare_at_price visible uniquement dans la fenêtre)
- Horizon configuré, retries, logs

**Fait quand** : une commande test déclenche un WhatsApp en < 60 s, la réponse « 1 » passe la commande en confirmée sans intervention.

### Sprint 5 — CMS, SEO, tracking, perf

- PageResource avec éditeur par blocs (texte, image, colonnes, FAQ accordéon) ; pages seedées dans le ton Maison Indigo (vouvoiement)
- Contact (formulaire + lien WhatsApp), membres (formulaire → `members` + `MailerLiteService`)
- `SeoService` : titles/meta par gabarit, canonical, Open Graph, sitemap.xml, robots.txt, JSON-LD Organization/Breadcrumb
- `useTracking` : dataLayer + événements `view_item`, `add_to_cart`, `begin_checkout`, `purchase`, `size_quiz_completed` ; Meta CAPI server-side via `SendMetaCapiEvent` ; bandeau consentement
- Perf : SSR vérifié, images `srcset` AVIF/WebP, lazy, `fetchpriority` hero, cache Redis des collections, Lighthouse mobile ≥ 90

**Fait quand** : Lighthouse mobile ≥ 90 sur home, collection, produit ; événements visibles dans Meta Events Manager ; pages éditables sans code.

### Sprint 6 — QA, sécurité, production

- Tests : couverture Pest sur Cart, Checkout, Discount, Shipping, OrderStatus, WhatsApp webhook ; tests navigateur (Pest browser ou Playwright) sur le parcours d'achat
- Sécurité : rate limiting checkout/webhooks, validation stricte, CSRF, headers, signature webhook Meta, 2FA Filament
- Accessibilité : clavier, contrastes, labels, focus
- Production : VPS, Coolify, Cloudflare, SSL, sauvegardes MySQL + médias vers S3 quotidiennes, Horizon en supervisor, logs (Laravel Pulse), alertes uptime
- Import du vrai catalogue (43 refs), recette contenu, `docs/QA-report.md`, tag `v1.0.0`

**Fait quand** : checklist §6 cochée, 10 commandes réelles de test en production sans anomalie.

---

## 5. Prompts par sprint pour Claude Code

Un prompt par session. Le prompt du Sprint 0 est fourni séparément (démarrage). Pour les suivants :

**Sprint 1**
> Lis CLAUDE.md et la section Sprint 1 de docs/MaisonIndigo_MVP_Roadmap.md. Crée les Filament Resources Product (avec relation manager variantes et médias ordonnés), Collection, SizeChart, ShippingZone et Setting. Écris DemoCatalogSeeder (10 produits réalistes) et les commandes artisan mi:import-catalog et mi:check-images. Construis Collection/Show.vue avec filtres, tri, pagination et MiProductCard, puis la page d'accueil. Tests Pest sur les filtres. Termine par la liste de ce que je dois vérifier visuellement.

**Sprint 2**
> Lis CLAUDE.md et la section Sprint 2. Développe Product/Show.vue avec MiGallery, MiSizeSelector (rupture visible + « Me prévenir »), bloc matière, MiSizeChart, « Complète le look », bloc livraison et JSON-LD. Puis le panier complet : actions, composable useCart, MiCartDrawer, persistance invité/compte, DiscountEngine avec bundle 2 jeans -10 %. Tests Pest sur Cart et DiscountEngine. Prépare la config Coolify pour un staging.

**Sprint 3**
> Lis CLAUDE.md et la section Sprint 3. Implémente le checkout une page (invité/compte, adresse, ShippingCalculator, COD/virement, création de commande transactionnelle avec numérotation MI-AAAA-NNNNNN), la page Merci, Account/Orders, les notifications e-mail. Puis SizeQuiz.vue avec useSizeQuiz et data/size-charts.json. Enfin OrderResource dans Filament avec kanban par statut et transitions contrôlées. Tests Pest sur Checkout et transitions de statut.

**Sprint 4**
> Lis CLAUDE.md et la section Sprint 4. Crée WhatsAppService (Meta Cloud API), le job SendWhatsAppMessage, le webhook de réception avec vérification de signature, et le passage automatique en confirmée / à rappeler. Ajoute les messages expédition et retour en stock, les alertes stock bas, DiscountResource avec codes, bundles et gabarit vente privée. Configure Horizon. Tests Pest sur le webhook et le DiscountEngine étendu. Aucun token en dur.

**Sprint 5**
> Lis CLAUDE.md et la section Sprint 5. Construis PageResource avec éditeur par blocs et seed les pages dans le ton Maison Indigo (vouvoiement, pas d'exclamation). Ajoute Contact, membres + MailerLiteService, SeoService complet (meta, canonical, OG, sitemap, JSON-LD), useTracking avec les 5 événements, Meta CAPI server-side, bandeau consentement. Puis une passe perf : SSR, images responsive, cache Redis, objectif Lighthouse mobile ≥ 90.

**Sprint 6**
> Lis CLAUDE.md et la section Sprint 6. Complète la couverture Pest, ajoute des tests navigateur sur le parcours d'achat, durcis la sécurité (rate limiting, signatures, 2FA Filament, headers), fais une passe accessibilité, écris la config production Coolify (Horizon, sauvegardes, Pulse), puis produis docs/QA-report.md avec ce qui est fait et ce qui reste à valider manuellement.

---

## 6. Checklist de lancement

**Légal & comptes**
- [ ] Dépôt OMPIC « Maison Indigo » semi-figurative, classes 25 + 35
- [ ] maisonindigo.ma sur Cloudflare, SSL, e-mails bonjour@ configurés (SPF/DKIM/DMARC)
- [ ] Mentions légales, CGV, retours 14 j, confidentialité relues
- [ ] Compte WhatsApp Business API vérifié, templates approuvés par Meta

**Boutique**
- [ ] 43 refs importées, 6 photos chacune, stocks saisis
- [ ] 11 tableaux de mesures publiés, quiz calibré
- [ ] Zones et tarifs de livraison testés (40 dh / offerte ≥ 600 dh)
- [ ] COD + virement (RIB) actifs, e-mails de commande relus
- [ ] Bundle 2 jeans -10 % et code INDIGO10 actifs, aucun prix barré hors vente privée

**Tech**
- [ ] Lighthouse mobile ≥ 90 sur 3 gabarits
- [ ] CI verte, `composer test` vert, Larastan niveau 6
- [ ] Sauvegardes testées (restauration réelle), Horizon en supervisor, alertes uptime
- [ ] Pixels + CAPI vérifiés sur une commande test
- [ ] Tag `v1.0.0`

**Marketing**
- [ ] @maisonindigo Instagram / TikTok, bio, lien
- [ ] Liste membres ≥ 500
- [ ] 6 créateurs produits reçus, budget J0 validé

---

## 7. Phase 2 (dans l'ordre)

1. Panier abandonné WhatsApp (job planifié)
2. CMI / carte bancaire
3. Avis clients avec photos
4. Arabe RTL (i18n déjà prête)
5. Fidélité / parrainage 100 dh
6. Recherche Meilisearch + Scout
7. Google Shopping (flux XML)
8. Diaspora : EUR, livraison Europe
