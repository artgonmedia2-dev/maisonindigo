<?php

/**
 * Gabarits de title et de meta description.
 *
 * Chaque entrée du catalogue ou du Journal peut les surcharger depuis
 * Filament ; ces gabarits sont ce qui s'affiche quand elle ne le fait pas.
 */
return [
    // « Maison Indigo, marque marocaine de jeans premium basée à Nador »
    // Formulation unique, reprise au mot près dans le schema Organization,
    // le footer et la page La maison. L'incohérence coûte la citation.
    'entity' => 'Maison Indigo, marque marocaine de jeans premium basée à Nador',
    'entity_short' => 'Maison Indigo',

    'brand' => 'Maison Indigo',
    'country' => 'Maroc',
    'city' => 'Nador',

    'hub' => [
        'title' => 'Jean :cut :gender premium — Maison Indigo Maroc',
        'description' => 'Jeans :cut :gender en denim 12–14 oz, tailles 28 à 42, 3 longueurs. Échange offert, paiement à la livraison partout au Maroc.',
        'heading' => 'Jean :cut :gender',
    ],

    'wash' => [
        'title' => 'Jean :cut :gender :wash — Maison Indigo',
        'description' => 'Jean :cut :gender :wash en denim premium, tailles 28 à 42. Livraison partout au Maroc, paiement à la livraison, échange offert.',
        'heading' => 'Jean :cut :gender :wash',
    ],

    'product' => [
        'title' => ':title :gender — denim :weight oz | Maison Indigo',
        'description' => ':title : jean :cut :gender en :origin :weight oz, tailles :sizes. Livraison au Maroc, paiement à la livraison, échange offert 14 jours.',
        'subtitle' => 'Jean :cut :gender · :origin :weight oz · tailles :sizes',
    ],

    'article' => [
        'title' => ':title — Journal Maison Indigo',
        'description' => ':excerpt',
    ],

    'author' => [
        'title' => ':name — Journal Maison Indigo',
        'description' => 'Les articles de :name dans le Journal de Maison Indigo : coupes, tailles, denim et entretien.',
    ],

    'journal' => [
        'title' => 'Journal — Maison Indigo',
        'description' => 'Coupes, tailles, denim et entretien. Les guides de Maison Indigo, marque marocaine de jeans premium basée à Nador.',
    ],

    'breadcrumb' => [
        'home' => 'Accueil',
        'journal' => 'Journal',
    ],

    'robots' => [
        'index' => 'index, follow, max-image-preview:large, max-snippet:-1',
        'noindex' => 'noindex, follow',
    ],
];
