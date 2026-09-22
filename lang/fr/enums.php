<?php

return [
    'gender' => [
        'homme' => 'Homme',
        'femme' => 'Femme',
    ],
    'order_status' => [
        'new' => 'Nouvelle',
        'confirmed' => 'Confirmée',
        'to_callback' => 'À rappeler',
        'prepared' => 'Préparée',
        'shipped' => 'Expédiée',
        'delivered' => 'Livrée',
        'cancelled' => 'Annulée',
        'returned' => 'Retournée',
    ],
    'payment_method' => [
        'cod' => 'Paiement à la livraison',
        'transfer' => 'Virement bancaire',
    ],
    'product_status' => [
        'draft' => 'Brouillon',
        'active' => 'En ligne',
        'archived' => 'Archivé',
    ],
    'collection_type' => [
        'manual' => 'Manuelle',
        'rule' => 'Par règles',
    ],
    'discount_type' => [
        'percent' => 'Pourcentage',
        'fixed' => 'Montant fixe',
        'bundle' => 'Lot',
    ],
    'whatsapp_direction' => [
        'outbound' => 'Envoyé',
        'inbound' => 'Reçu',
    ],
];
