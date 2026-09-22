<?php

/*
|--------------------------------------------------------------------------
| Textes côté serveur de la boutique
|--------------------------------------------------------------------------
|
| Vouvoiement, phrases courtes, pas de point d'exclamation.
| Les textes des composants Vue vivent dans resources/js/i18n/fr.ts.
|
*/

return [
    'name' => 'Maison Indigo',
    'tagline' => 'Le bleu, bien coupé.',

    'meta' => [
        'home_description' => 'Jeans premium fabriqués pour le Maroc. Denim de 12 à 14 oz, tailles 26 à 42 en trois longueurs, échange offert sous 14 jours, paiement à la livraison.',
    ],

    'nav' => [
        'women' => 'Femme',
        'men' => 'Homme',
        'new' => 'Nouveautés',
        'atelier' => 'Atelier',
        'size_quiz' => 'Trouver ma taille',
    ],

    'collections' => [
        'women' => [
            'title' => 'Femme',
            'lead' => 'Wide leg, straight, mom, slim, bootcut, flare. Taille haute, denim de 12 à 14 oz, du 26 au 40.',
            'description' => 'Jeans femme Maison Indigo : six coupes, taille haute, denim japonais, turc et italien. Tailles 26 à 40, trois longueurs.',
        ],
        'men' => [
            'title' => 'Homme',
            'lead' => 'Straight, regular, slim, relaxed, tapered. Taille mi-haute, denim de 12 à 14 oz, du 28 au 42.',
            'description' => 'Jeans homme Maison Indigo : cinq coupes, denim japonais, turc et italien. Tailles 28 à 42, trois longueurs.',
        ],
        'new' => [
            'title' => 'Nouveautés',
            'lead' => 'Les dernières coupes entrées à l’atelier. Les premières photographies arrivent.',
            'description' => 'Les nouvelles coupes Maison Indigo, femme et homme. Denim premium, tailles 26 à 42.',
        ],
        'atelier' => [
            'title' => 'Atelier',
            'lead' => 'Petites séries tissées en Italie ou au Japon, sans élasthanne. Quand elles sont parties, elles sont parties.',
            'description' => 'Les éditions de l’atelier Maison Indigo : denim italien et japonais en petite série.',
        ],
        'sort' => [
            'new' => 'Nouveautés d’abord',
            'price_asc' => 'Prix croissant',
            'price_desc' => 'Prix décroissant',
        ],
    ],

    'product' => [
        'alert_registered' => 'C’est noté. Nous vous prévenons dès que cette taille revient.',
    ],

    'cart' => [
        'title' => 'Votre panier',
        'added' => ':title, taille :size, est dans votre panier.',
        'out_of_stock' => ':title en :size n’est plus disponible.',
        'stock_limited' => 'Il ne reste que :count exemplaire(s) de :title en :size.',
        'code_invalid' => 'Ce code ne s’applique pas à votre panier.',
    ],

    'checkout' => [
        'title' => 'Votre commande',
        'confirmation_title' => 'Merci, votre commande est enregistrée.',
        'phone_invalid' => 'Indiquez un numéro de mobile marocain, par exemple 06 12 34 56 78.',
        'history_created' => 'Commande passée sur la boutique, :method.',
        'payment' => [
            'cod' => 'Vous réglez en espèces au livreur, à la réception de votre boîte.',
            'transfer' => 'Vous recevez nos coordonnées bancaires ; nous expédions à réception du virement.',
        ],
    ],

    'whatsapp' => [
        'cod_confirmation' => "Bonjour :name, ici Maison Indigo.\n\nVotre commande :number :\n:lines\n\nTotal à régler au livreur : :total, livraison à :city.\n\nRépondez 1 pour confirmer, ou 2 si vous souhaitez modifier quelque chose.",
    ],

    'alerts' => [
        'order_title' => 'Nouvelle commande :number',
        'order_open' => 'Ouvrir la commande',
        'order_test' => 'Message d’essai de Maison Indigo. Les alertes de commande arriveront ici.',
    ],

    'size_quiz' => [
        'title' => 'Trouver ma taille',
        'description' => 'Quatre questions, une recommandation précise : votre coupe, votre taille et votre longueur Maison Indigo.',
        'hips' => [
            'etroites' => 'Plutôt étroites',
            'moyennes' => 'Dans la moyenne',
            'larges' => 'Plutôt larges',
        ],
        'fit' => [
            'ajuste' => 'Ajusté',
            'droit' => 'Droit',
            'ample' => 'Ample',
        ],
        'fit_description' => [
            'ajuste' => 'Près du corps, de la hanche à la cheville.',
            'droit' => 'La ligne classique, ni serrée ni large.',
            'ample' => 'De l’air aux cuisses et à la jambe.',
        ],
        'advice_fitted' => 'Sur une coupe ajustée, prenez la taille indiquée : le denim se détend d’un demi-centimètre en une semaine.',
        'advice_hips' => 'Nous avons compté vos hanches. Si la taille baille à l’arrière, un passage chez le retoucheur règle la question.',
        'advice_default' => 'Entre deux tailles, choisissez la plus petite : le tissu se détend légèrement.',
    ],

    'fields' => [
        'variant' => 'taille',
        'qty' => 'quantité',
        'name' => 'nom complet',
        'phone' => 'numéro de téléphone',
        'email' => 'adresse e-mail',
        'line1' => 'adresse',
        'city' => 'ville',
        'payment_method' => 'mode de paiement',
        'discount_code' => 'code',
        'gender' => 'collection',
        'waist_cm' => 'tour de taille',
        'height_cm' => 'hauteur',
        'hips' => 'hanches',
        'fit' => 'tombé',
    ],

    'flash' => [
        'saved' => 'Vos modifications sont enregistrées.',
        'error' => 'Une erreur est survenue. Réessayez dans un instant.',
    ],
];
