# Maison Indigo — Audit final

Revue complète du code, de la sécurité, des performances et de l'écart au périmètre MVP.
Établie le 21 septembre 2026, sur la branche `main`, après 26 commits.

---

## 1. Verdict

La boutique est **fonctionnelle et livrable**. Le parcours d'achat complet tient debout : catalogue, fiche produit, panier, commande en quatre champs, paiement à la livraison, suivi en back-office. La qualité du code est au niveau demandé par `CLAUDE.md` : analyse statique au niveau 6 sans erreur, 180 tests verts, aucune dette signalée dans le code.

Ce qui manque relève des sprints 4 à 6 du roadmap, jamais du socle : le webhook WhatsApp entrant, les e-mails transactionnels et le tracking publicitaire.

| Indicateur | Valeur |
|---|---|
| Fichiers PHP applicatifs | 154 (9 258 lignes) |
| Fichiers Vue et TypeScript | 71 (5 685 lignes) |
| Modèles | 22 |
| Migrations | 18, plus 1 migration de réglages |
| Routes publiques | 69 |
| Fichiers de test | 31 |
| Tests | 180, tous verts, exécutés deux fois de suite sans instabilité |
| Pint | Aucun écart |
| Larastan niveau 6 | Aucune erreur |
| Commentaires `TODO` ou `FIXME` | 0 |

---

## 2. Formulaire de commande réduit à quatre champs

Demandé et livré. Le client ne saisit plus que :

| Champ | Validation |
|---|---|
| Nom complet | 3 à 120 caractères |
| Numéro de téléphone | Mobile marocain : `06`, `07`, `+2126`, `+2127`, `00212` |
| Ville | Liste de suggestion alimentée par les zones de livraison |
| Adresse | 5 à 190 caractères |

**Supprimés** : adresse e-mail, complément d'adresse, région, remarques pour le livreur, case à cocher des conditions de vente.

Le mode de paiement reste, il détermine le parcours après commande. Le champ code de remise est replié derrière un lien « J'ai un code ». L'acceptation des conditions passe d'une case à une phrase sous le bouton, ce qui est la pratique courante et retire un clic.

**Conséquences à connaître**

- L'adresse figée sur la commande ne contient plus que nom, téléphone, ville, adresse et zone. Les commandes déjà enregistrées gardent leur ancienne forme, le back-office les affiche sans erreur.
- Sans e-mail, un client invité ne peut pas recevoir de confirmation par courrier. Le canal de confirmation est WhatsApp, conformément au positionnement marocain du projet. Les clients qui créent un compte gardent leur e-mail.
- Le champ « remarques du livreur » disparaît de la fiche commande, remplacé par les notes internes de la maison.

---

## 3. Architecture

L'organisation demandée par `CLAUDE.md` est respectée sans exception.

- **Logique métier dans `app/Actions/`** : neuf actions, une classe et une méthode `handle` chacune. Les contrôleurs orchestrent, ils ne calculent pas.
- **Services sans état** : `ShippingCalculator`, `DiscountEngine`, `WhatsAppService`.
- **Enums PHP 8.3 adossés** : neuf, tous avec leur libellé traduit.
- **Prix en centimes entiers** partout, formatés au seul endroit prévu : `App\Support\Money` côté serveur, `useMoney()` côté Vue.
- **Props Inertia typées** : un fichier TypeScript par page, aucun `any`.
- **Composants de la maison préfixés `Mi`** : dix-sept, dans `resources/js/Components/mi/`.
- **Textes hors du code** : `lang/fr/*.php` et `resources/js/i18n/fr.ts`, avec clés typées côté TypeScript.

**Points forts particuliers**

- `PlaceOrder` fait tout dans une seule transaction, avec verrou `lockForUpdate` sur les variantes : deux commandes simultanées sur le dernier exemplaire ne peuvent pas passer toutes les deux.
- `TransitionOrderStatus` est l'unique porte d'entrée des changements de statut. Elle valide la transition, horodate, restitue le stock à l'annulation et écrit l'historique.
- `GenerateOrderNumber` verrouille la ligne de séquence : les numéros `MI-2026-000001` ne peuvent pas se dupliquer.
- Le chargement paresseux lève une exception hors production, ce qui rend un N+1 impossible à ignorer.

---

## 4. Sécurité

| Point | État | Détail |
|---|---|---|
| Validation systématique | Conforme | Sept `FormRequest`, aucune écriture depuis une entrée brute |
| Injection SQL | Conforme | Eloquent partout ; l'unique `whereRaw` utilise un paramètre lié |
| XSS | Conforme | Aucun `v-html` ni `innerHTML` dans le code |
| CSRF | Conforme | Middleware `web` sur toutes les routes de la boutique |
| Secrets | Conforme | Aucun `.env` suivi par git ; les fixtures ne contiennent pas de jeton |
| Assignation de masse | Conforme | `preventSilentlyDiscardingAttributes` actif hors production |
| Double authentification | Conforme | Disponible sur le back-office, à activer par l'administrateur |
| Séparation admin / client | Conforme | Table `admins` et garde dédiée, distinctes des `customers` |
| Fichiers sensibles exposés | Conforme | `.htaccess` refuse `.env`, `composer.json`, `vendor/`, `.git` |
| En-têtes de sécurité | Conforme | `nosniff`, `SAMEORIGIN`, `Referrer-Policy`, `Permissions-Policy`, HSTS |
| Limitation de débit | **Corrigé pendant l'audit** | Voir ci-dessous |

**Faille trouvée et fermée.** Les routes publiques `POST /register`, `POST /forgot-password` et `POST /reset-password` n'avaient aucune limitation. Un robot pouvait créer des comptes en masse ou provoquer un envoi massif d'e-mails de réinitialisation depuis votre domaine, avec le risque de faire classer ce domaine comme expéditeur indésirable. Un limiteur `account` à cinq tentatives par minute et par adresse IP a été ajouté, avec un test de non-régression.

Récapitulatif des limiteurs en place : `checkout` (5/min), `cart` (60/min), `stock-alert` (5/min), `size-quiz` (20/min), `account` (5/min), plus le verrouillage progressif de la connexion (5 essais).

**Reste à faire avant l'ouverture au public**

1. Activer la double authentification sur le compte administrateur.
2. Changer le mot de passe MySQL et celui de l'administrateur : tous deux ont circulé en clair pendant la mise en ligne.
3. Vérifier que `https://votre-domaine/.env` répond 403 ou 404.

---

## 5. Performance

- **Images** : conversions WebP et AVIF, `srcset` responsive, `loading="lazy"` partout sauf l'image d'accueil qui porte `fetchpriority="high"`. L'image d'accueil pèse 13 à 72 ko selon la largeur servie.
- **Polices** : auto-hébergées, sous-ensemble latin, `font-display: swap`, préchargées dans l'en-tête.
- **Cache** : les listes de collection passent par `CatalogCache`, dont un numéro de version est incrémenté à chaque sauvegarde produit ou variante. Une modification invalide donc tout, sans purge manuelle. Les zones de livraison sont également en cache, invalidées par observateur.
- **Base** : 40 index et contraintes déclarés, y compris les index composites `status + gender`, `status + is_new`, `product_id + stock`.
- **Requêtes** : chargement anticipé explicite sur toutes les ressources Filament et tous les contrôleurs de la boutique.

**Limite assumée.** Le rendu côté serveur est désactivé sur hébergement mutualisé, faute de processus Node permanent. Les pages restent indexables, mais le premier affichage dépend du navigateur. Le score Lighthouse mobile sera un peu en deçà de la cible de 90 fixée par le roadmap. Le passage sur VPS lève cette limite sans toucher au code, par simple changement de `.env`.

---

## 6. Référencement

| Élément | État |
|---|---|
| Métadonnées par gabarit | En place, titre et description par page |
| `sitemap.xml` | Généré dynamiquement, produits inclus |
| `robots.txt` | Présent |
| Balisage JSON-LD | Partiel : `ClothingStore` global uniquement |
| Langue déclarée | `fr` |
| Hiérarchie des titres | Correcte, un seul `h1` par page |

**À compléter** : le balisage `Product` sur la fiche produit, `BreadcrumbList` sur le fil d'Ariane et `Organization` avec le logo. C'est prévu au sprint 5 et représente une demi-journée.

---

## 7. Accessibilité

Bon niveau, rarement atteint sur une boutique de cette taille.

- Lien d'évitement vers le contenu, présent sur toutes les pages.
- Focus visible : contour indigo de 2 px, décalé de 3 px, conforme à la charte.
- `prefers-reduced-motion` respecté globalement.
- Sélecteur de taille en `radiogroup` avec `aria-checked`, une taille en rupture reste annoncée comme telle plutôt que masquée.
- Messages d'erreur reliés aux champs par `aria-describedby`, zones d'état en `aria-live`.
- Propriétés logiques CSS (`inline-start`, `ms-`, `me-`) partout, ce qui prépare l'arabe sans réécriture.

**À vérifier à la main** : le contraste du gris `#8A8F98` sur fond écru donne un rapport d'environ 3,1 pour 1. C'est conforme pour du texte large, insuffisant pour du texte courant sous 18 px. Il est utilisé pour des mentions secondaires, mais mériterait un assombrissement à `#767B84`.

---

## 8. Tests

180 tests, exécutés deux fois consécutives sans écart. Répartition :

| Domaine | Fichiers |
|---|---|
| Boutique (collections, produit, panier, commande, quiz) | 7 |
| Back-office Filament | 5 |
| Authentification et compte | 7 |
| Services métier | 3 |
| Commandes et transitions | 2 |
| Seeders | 1 |
| Déploiement et réglages | 4 |

**Couvert** : parcours d'achat de bout en bout, calcul de livraison, moteur de remises, transitions de statut interdites, restitution du stock, rédaction du message WhatsApp, écrans du back-office, idempotence des réglages, contrôle de mise en ligne.

**Correction apportée pendant l'audit.** La fabrique de produits pouvait tirer une combinaison genre × coupe × lavage déjà créée par le catalogue de démonstration, ce qui faisait échouer un test au hasard. Elle consulte désormais la base et écarte les combinaisons prises. Deux exécutions complètes confirment la stabilité.

**Non couvert** : aucun test unitaire isolé sur les actions du panier et sur `PlaceOrder`. Elles sont éprouvées à travers les tests de parcours, ce qui suffit pour détecter une régression, mais localise moins bien la cause d'un échec.

---

## 9. Écart au périmètre MVP

| Bloc du roadmap | État |
|---|---|
| Catalogue, filtres, tri, pagination | Livré |
| Fiche produit, sélecteur de taille, rupture visible | Livré |
| Trouver ma taille | Livré |
| Panier tiroir, persistant, remise automatique | Livré |
| Commande en une page, invité par défaut | Livré, réduit à quatre champs |
| Statuts de commande, historique, notes internes | Livré |
| Back-office Filament complet | Livré |
| Comptes clients | Livré |
| Pages CMS | Livré en pages statiques, non éditables dans Filament |
| **Confirmation COD par WhatsApp** | **Partiel** : l'envoi part, le webhook de réponse manque |
| **E-mails transactionnels** | **Absent** : aucune notification ni aucun mailable |
| **Tracking** | **Absent** : ni pixel Meta, ni TikTok, ni GA4, ni bandeau de consentement |
| Barre de livraison offerte, échange 14 jours | Livré, pilotable depuis les réglages |

**Trois chantiers restants, dans cet ordre de valeur**

1. **Webhook WhatsApp** (sprint 4, deux jours). Sans lui, la réponse « 1 » du client n'est pas lue : la confirmation reste manuelle dans le back-office. C'est le chaînon qui rend le paiement à la livraison réellement automatique.
2. **E-mails** (sprint 5, un jour). Confirmation de commande, expédition, retour en stock. D'autant plus utile que le formulaire ne collecte plus d'e-mail pour les invités : ces envois ne concerneront que les clients avec un compte.
3. **Tracking et consentement** (sprint 5, un jour). Indispensable avant toute dépense publicitaire, sans quoi vous ne saurez pas ce qui convertit.

---

## 10. Déploiement

Le socle est solide après les difficultés de la première mise en ligne, toutes documentées et outillées.

- `bash deploy/hostinger/build-package.sh` fabrique une archive de 22 Mo prête à extraire dans `public_html`.
- `php artisan mi:deploy-check` contrôle PHP, environnement, base, droits, assets et contenu, et sort en erreur tant qu'un point bloque.
- `artisan` refuse de démarrer sous PHP 8.2 avec la marche à suivre en français.
- La migration des réglages est rejouable sans écraser les valeurs saisies.

**Pièges déjà neutralisés** : version PHP du site différente de celle du terminal, application déposée à la racine de `public_html`, `view:cache` incompatible avec la page de connexion personnalisée, `npx` résolvant hors du projet, `npm ci` et `zip` indisponibles sous Windows.

---

## 11. Recommandations classées

**Avant l'ouverture au public**

1. Activer la double authentification sur le compte administrateur.
2. Changer le mot de passe MySQL et celui de l'administrateur.
3. Retirer la barre oblique finale d'`APP_URL`.
4. Installer la tâche cron avec le chemin complet du binaire PHP 8.3, sinon la file d'attente ne tourne pas.
5. Saisir le vrai catalogue et remplacer les dix produits de démonstration.

**Dans le mois**

6. Webhook WhatsApp entrant.
7. E-mails transactionnels.
8. Pixels et bandeau de consentement.
9. Balisage JSON-LD `Product` et `BreadcrumbList`.
10. Assombrir le gris secondaire pour le contraste.

**Quand le volume le justifiera**

11. Passer sur VPS pour retrouver le rendu serveur et Horizon : seul le `.env` change.
12. Ajouter des tests unitaires sur les actions du panier et sur `PlaceOrder`.
13. Rendre les pages CMS éditables dans Filament, aujourd'hui figées dans des composants Vue.

---

## 12. Ce qui mérite d'être souligné

Trois choix se révèlent particulièrement justes à l'usage.

Le **prix en centimes entiers** avec un seul point de formatage de chaque côté élimine toute une famille de bugs d'arrondi, invisibles jusqu'au jour où ils coûtent de l'argent.

La **transition de statut par une action unique** rend impossible une commande expédiée sans avoir été confirmée, ou un stock restitué deux fois. La règle est écrite une fois, dans l'enum, et testée.

Le **contrôle de mise en ligne automatisé** transforme un déploiement à l'aveugle en une liste de points vérifiés, avec la correction affichée à côté de chaque problème. Le temps passé à l'écrire a déjà été regagné.
