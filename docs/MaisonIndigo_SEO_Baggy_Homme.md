# MAISON INDIGO — Structure SEO / GEO
## Cluster « jean baggy homme » — marché Maroc (Google + moteurs génératifs)

Version 1.0 · Septembre 2026 · ARTGON MEDIA

---

## 1. Lecture des mots-clés

| Mot-clé | Intention | Ce qu'il faut pour ranker | Décision |
|---------|-----------|---------------------------|----------|
| **baggy jeans homme** / jeans baggy homme / jean baggy homme | Commerciale : l'utilisateur veut voir des baggys à acheter | Une page **collection** riche (grille + contenu + FAQ), pas un article | **Page hub** `/homme/jean-baggy` |
| **jean baggy homme levi's** | Navigationnelle de marque : il cherche Levi's, pas nous | Impossible avec une page produit ; possible avec un **comparatif honnête** | Article « Levi's baggy homme : modèles, prix au Maroc et alternatives » |
| **jean baggy homme pas cher** | Transactionnelle sensible au prix | Conflit avec le positionnement 399–599 dh ; on ne ment pas sur « pas cher » | Article **guide prix** « Jean baggy homme : quel prix au Maroc et que vaut un baggy à 300 dh » + page bundle |
| Alternatives (variantes de formulation) | Même intention que le hub | Absorbées par le hub via le contenu et les H2 | Intégrées, pas de pages séparées |

**Alternatives à intégrer dans le hub** (formulations, pas des pages) : jean large homme · jean ample homme · jean loose homme · jean oversize homme · baggy homme · pantalon baggy homme · jean baggy homme maroc · jean baggy homme casablanca · jean baggy homme taille haute · jean baggy homme streetwear · baggy jeans homme 2026.

**Variantes qui méritent leur propre page** (intention distincte) :
- par lavage : jean baggy homme **noir**, jean baggy homme **bleu**, jean baggy homme **brut**, jean baggy homme **clair/délavé** → sous-collections
- par coupe voisine : jean **wide leg** homme, jean **relaxed** homme → collections sœurs, reliées au hub
- informationnelles : comment porter un jean baggy homme · baggy vs wide leg vs relaxed · quelle taille pour un baggy · avec quoi porter un baggy · entretien → articles du Journal

---

## 2. Architecture : hub & spokes

```
/homme/jean-baggy                          ← HUB (collection) — « jean baggy homme »
├── /homme/jean-baggy/noir                 ← sous-collection lavage — « jean baggy homme noir »
├── /homme/jean-baggy/bleu                 ← « jean baggy homme bleu »
├── /homme/jean-baggy/brut                 ← « jean baggy homme brut »
├── /homme/jean-baggy/clair                ← « jean baggy homme clair »
├── /homme/baggy-indigo-brut               ← produits (Coupe + Lavage), 1 par lavage
├── /homme/baggy-noir
├── /homme/baggy-stone
├── /homme/jean-wide-leg                   ← collection sœur, maillée avec le hub
├── /homme/jean-relaxed                    ← collection sœur
└── /journal/
    ├── comment-porter-jean-baggy-homme          ← « comment porter un jean baggy homme »
    ├── baggy-wide-leg-relaxed-differences       ← « différence baggy wide leg »
    ├── quelle-taille-jean-baggy-homme           ← « quelle taille pour un baggy », « baggy taille haute ou basse »
    ├── jean-baggy-homme-prix-maroc              ← « jean baggy homme pas cher », « prix jean baggy maroc »
    ├── levis-baggy-homme-maroc-alternatives     ← « jean baggy homme levi's »
    └── entretien-jean-baggy-denim-brut          ← « laver un jean brut »
```

Règles :
- **Un seul hub** pour toutes les formulations du terme principal. Pas de page « jeans baggy homme » distincte de « jean baggy homme » : Google les fusionne, vous cannibaliseriez.
- Les **facettes indexables** sont uniquement les lavages (4 pages max). Tailles, prix, tri = `noindex, follow` + canonical vers le hub.
- Femme : même structure en miroir `/femme/jean-baggy` avec son propre cluster (« jean baggy femme » a une demande distincte).
- Fil d'Ariane partout : Accueil › Homme › Jean baggy › Baggy Indigo Brut.

---

## 3. Structure on-page du hub `/homme/jean-baggy`

**Title** (≤ 60 car.) : `Jean baggy homme premium — Maison Indigo Maroc`
**Meta description** (≤ 155) : `Jeans baggy homme en denim 12–14 oz, tailles 28 à 42, 3 longueurs. Échange offert, paiement à la livraison partout au Maroc.`
**URL** : `/homme/jean-baggy`
**H1** : `Jean baggy homme`

**Bloc 1 — Intro (80–120 mots, au-dessus de la grille)**
Définit ce qu'est un baggy chez Maison Indigo (coupe, tombé, denim, tailles), mentionne Maroc et livraison. Contient naturellement : jean baggy homme, jean large, denim, tailles 28–42. Pas de liste de mots-clés.

**Bloc 2 — Grille produits** (`ItemList` schema)
Cartes : nom Coupe + Lavage, matière, prix, tailles disponibles. Filtres lavage / taille / longueur (facettes non indexées sauf lavage).

**Bloc 3 — Contenu enrichi (600–900 mots, sous la grille, en sections dépliables sur mobile)**

- **H2 — Comment tombe notre baggy** : taille mi-haute, cuisse ample, ouverture de jambe en cm, comparaison rapide avec wide leg et relaxed (lien vers l'article différences)
- **H2 — Quel lavage choisir** : brut, bleu, noir, clair — 2 lignes chacun, lien vers chaque sous-collection
- **H2 — Trouver sa taille en baggy** : conseil entre-deux, longueurs 30/32/34, lien vers le quiz et le guide taille
- **H2 — Avec quoi porter un jean baggy** : 3 silhouettes en 3 phrases, lien vers l'article « comment porter »
- **H2 — Denim et fabrication** : origine, grammage, finitions — les entités que les moteurs génératifs retiennent
- **H2 — Livraison et échange au Maroc** : délais par ville, COD, échange offert 14 jours

**Bloc 4 — FAQ (`FAQPage` schema, 6–8 questions)**
- Quelle est la différence entre un jean baggy et un jean large ?
- Un jean baggy homme taille grand ou petit ?
- Quelle longueur choisir pour un baggy ?
- Comment laver un baggy en denim brut ?
- Livrez-vous à Casablanca, Rabat, Tanger, Marrakech, Agadir, Oujda ?
- Peut-on payer à la livraison ?
- Peut-on échanger la taille ?
- Combien coûte un jean baggy homme de qualité au Maroc ?

**Bloc 5 — Maillage sortant**
Collections sœurs (wide leg, relaxed, straight), sous-collections lavage, 3 articles du Journal, quiz taille.

**Schemas** : `CollectionPage` + `ItemList` (produits) + `FAQPage` + `BreadcrumbList` + `Organization` (site-wide).

---

## 4. Structure des sous-collections lavage `/homme/jean-baggy/noir`

- Title : `Jean baggy homme noir — Maison Indigo`
- H1 : `Jean baggy homme noir`
- Intro 60–80 mots spécifique au lavage (tenue de la couleur, avec quoi le porter)
- Grille filtrée
- 3 questions FAQ propres au lavage (le noir déteint-il, comment le garder noir)
- Canonical vers elle-même, lien vers le hub et les autres lavages

---

## 5. Structure des pages produit `/homme/baggy-indigo-brut`

- Title : `Baggy Indigo Brut homme — denim 13 oz | Maison Indigo`
- H1 : `Baggy Indigo Brut`
- Sous-titre : `Jean baggy homme · denim japonais 13 oz · tailles 28–42`
- Description 150–250 mots : coupe, tombé, matière, taille du modèle, conseil taille, entretien
- Tableau des mesures (HTML `<table>`, pas image) — les moteurs génératifs adorent
- Schema `Product` complet : `offers` (MAD, disponibilité par variante), `brand`, `material`, `size`, `color`, `aggregateRating` quand les avis existent
- Fil d'Ariane, « Complète le look », lien vers le hub

---

## 6. Gabarit d'article du Journal (spokes informationnels)

Exemple : `/journal/comment-porter-jean-baggy-homme`

- Title : `Comment porter un jean baggy homme : 5 silhouettes qui marchent`
- H1 identique, **réponse directe en 2–3 phrases** sous le H1 (bloc « En bref ») — c'est ce que les moteurs génératifs citent
- H2 par silhouette (baggy + t-shirt ajusté, baggy + chemise denim, baggy + veste courte…), chaque section = 80–120 mots + 1 photo lifestyle Maison Indigo
- H2 « Les erreurs à éviter » (liste courte)
- H2 « Quelle taille pour que le baggy tombe bien » → lien quiz
- Encadré produit : 3 baggys du catalogue (`ItemList`)
- FAQ 4 questions (`FAQPage`)
- Auteur nommé (page auteur), date, mise à jour
- 900–1 400 mots, images avec alt descriptifs, aucune promo

**Articles prioritaires et angle**

| Article | Angle | Mot-clé principal |
|---------|-------|-------------------|
| Prix d'un jean baggy au Maroc | Grille de prix honnête 150 → 1 200 dh, ce que change le grammage et la coupe, quand « pas cher » coûte cher (retours, déformation) | jean baggy homme pas cher · prix jean baggy maroc |
| Levi's baggy homme au Maroc | Modèles Levi's baggy existants, prix constatés, où les trouver, puis « si vous cherchez un baggy premium fabriqué pour le Maroc » — comparatif tableau, factuel | jean baggy homme levi's · levi's baggy maroc |
| Baggy, wide leg, relaxed : les différences | Schéma des 3 coupes, mesures d'ouverture de jambe, pour quelle morphologie | différence baggy wide leg · jean large homme |
| Quelle taille pour un baggy | Guide mesure, tableau, entre-deux | quelle taille jean baggy · baggy taille haute |
| Comment porter un baggy | 5 silhouettes | comment porter un jean baggy homme |
| Entretien du denim brut | Lavage, délavage naturel, séchage | laver jean brut |

---

## 7. GEO — être cité par ChatGPT, Gemini, Perplexity, AI Overviews

Les moteurs génératifs ne classent pas des pages, ils extraient des **réponses** et des **entités**. Concrètement :

1. **Réponse directe en tête de chaque page** : 2–3 phrases factuelles qui répondent à la question du mot-clé, avant tout storytelling.
2. **Entités explicites et cohérentes** partout : « Maison Indigo, marque marocaine de jeans premium basée à Nador » — même formulation sur la page La maison, le footer, le schema `Organization`, LinkedIn, Instagram, fiche Google Business. L'incohérence tue la citation.
3. **Chiffres vérifiables** : grammage en oz, ouverture de jambe en cm, plage de tailles, délais par ville, prix en dh. Les modèles citent les pages qui donnent des nombres.
4. **Tableaux HTML** (mesures, comparatifs, prix) plutôt que du texte ou des images.
5. **FAQ structurées** (`FAQPage`) avec questions formulées comme les gens les posent à un assistant.
6. **Page « La maison »** riche : fondation, lieu, matière, procédé, engagements — c'est la source que les modèles utilisent pour décrire la marque.
7. **`llms.txt`** à la racine : présentation de la marque, liens vers le hub, les guides et la page La maison.
8. **Contenu comparatif honnête** (Levi's, prix du marché) : les modèles préfèrent citer une source qui compare qu'une source qui se vante.
9. **Auteur identifié** et page auteur (ARTGON MEDIA / responsable produit) pour les articles.
10. **Mentions externes** : fiche Google Business, annuaires marocains, 2–3 articles de presse ou blogs mode marocains reprenant la même description d'entité.

---

## 8. Maillage interne

```
Accueil ──► /homme ──► /homme/jean-baggy (hub)
                            │
        ┌───────────────────┼─────────────────────┐
        ▼                   ▼                     ▼
  sous-collections     produits baggy        collections sœurs
  (noir, bleu,         (indigo brut,         (wide leg, relaxed,
   brut, clair)         noir, stone…)         straight)
        │                   │                     │
        └────────┬──────────┴──────────┬──────────┘
                 ▼                     ▼
          Journal (6 articles) ◄──► Quiz taille
```

Chaque article pointe vers le hub avec l'ancre exacte « jean baggy homme » une fois, puis vers 2–3 produits. Le hub pointe vers chaque article avec une ancre descriptive. Aucune page orpheline.

---

## 9. Priorités et calendrier

| Semaine | Livrable |
|---------|----------|
| 1 | Réintroduire la coupe Baggy homme (3 lavages minimum) dans le catalogue ; créer le hub avec blocs 1–5 et schemas |
| 2 | 4 sous-collections lavage ; pages produit baggy complètes avec tableaux de mesures |
| 3 | Articles « différences baggy / wide leg / relaxed » et « quelle taille » |
| 4 | Article « prix au Maroc » (capte « pas cher ») et « comment porter » |
| 5 | Article « Levi's baggy au Maroc » ; page La maison enrichie ; `llms.txt` ; fiche Google Business |
| 6 | Miroir femme `/femme/jean-baggy` ; mesure : positions Semrush db=ma, impressions Search Console, citations dans Perplexity/ChatGPT (recherche manuelle mensuelle) |

**KPIs à 90 jours** : hub dans le top 10 sur « jean baggy homme » (Maroc), 3 articles dans le top 5 de leur requête, première citation de Maison Indigo dans une réponse générative sur « jean baggy homme maroc ».

---

## 10. Implémentation technique (rappel pour Claude Code)

- Routes : `/homme/jean-baggy`, `/homme/jean-baggy/{lavage}` (whitelist 4 valeurs), `/homme/{slug}` produits, `/journal/{slug}`
- `SeoService` : title/meta par gabarit surchargeables dans Filament par page
- Schemas JSON-LD générés côté serveur (SSR) : `CollectionPage` + `ItemList`, `Product`, `FAQPage`, `BreadcrumbList`, `Organization`, `Article`
- Facettes taille / prix / tri : `noindex, follow` + canonical hub
- Contenu enrichi et FAQ du hub éditables dans Filament (blocs), pas en dur
- `llms.txt`, `sitemap.xml` (collections, produits, articles), `robots.txt`
- Journal = `articles` table + `ArticleResource` Filament (auteur, date, mise à jour, blocs, FAQ)


---

## 11. État de l'implémentation — septembre 2026

| Élément | État | Où |
|---------|------|-----|
| Coupe baggy au catalogue | Fait | table `cuts`, code SKU `BAG`, homme et femme |
| Hub `/{genre}/jean-{coupe}` | Fait | `CutHubController@show`, page `Collection/Hub.vue` |
| Sous-collections lavage | Fait | `CutHubController@wash`, 4 segments, 404 ailleurs |
| Fiches produit `/{genre}/{slug}` | Fait | `ProductController@show`, 301 depuis `/produit/{slug}` |
| Journal | Fait | `JournalController`, `Journal/Index|Show|Author.vue` |
| Facettes `noindex, follow` | Fait | `BrowsesCatalog::isFaceted`, canonical vers l'adresse propre |
| Contenu enrichi éditable | Fait | `CollectionForm`, `WashPagesRelationManager` |
| Journal éditable | Fait | `ArticleResource`, `AuthorResource` |
| Schemas JSON-LD | Fait | `SeoService`, rendus dans `resources/views/partials/seo.blade.php` |
| `llms.txt`, `sitemap.xml`, `robots.txt` | Fait | `LlmsController`, `SitemapController`, `RobotsController` |
| Page La maison enrichie | Fait | entité + tableau de chiffres |
| Corps des six articles | À rédiger | back-office, le plan H2 et le « En bref » sont livrés |
| Photos produit et visuels d'articles | À charger | back-office |

**Écart assumé par rapport à la section 2** : le segment `/bleu` sert le lavage
`stone` du catalogue, qui est le bleu moyen de la maison. L'adresse reste celle
que les gens cherchent, sans inventer un lavage en double.

**Rendu des schemas** : ils sont produits dans le gabarit Blade et non dans Vue.
Le SSR Inertia est désactivé sur l'hébergement mutualisé ; un robot ou un moteur
génératif qui ne lit pas le JavaScript doit trouver le JSON-LD dans le HTML brut.
