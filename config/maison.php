<?php

/*
|--------------------------------------------------------------------------
| Maison Indigo
|--------------------------------------------------------------------------
|
| Réglages applicatifs lus depuis .env. Les réglages éditables par la maison
| (seuil de livraison offerte, textes légaux, pixels) vivent dans `settings`
| via Spatie Laravel Settings.
|
*/

return [
    'name' => 'Maison Indigo',

    'tagline' => 'Le bleu, bien coupé.',

    'admin' => [
        'name' => env('ADMIN_NAME', 'Maison Indigo'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    'contact' => [
        'email' => env('CONTACT_EMAIL', 'bonjour@maisonindigo.ma'),
        'whatsapp' => env('CONTACT_WHATSAPP', '+212600000000'),
        'city' => 'Casablanca',
    ],

    'whatsapp' => [
        'token' => env('WHATSAPP_TOKEN'),
        'phone_id' => env('WHATSAPP_PHONE_ID'),
        'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),
        'app_secret' => env('WHATSAPP_APP_SECRET'),
    ],

    'mailerlite' => [
        'token' => env('MAILERLITE_TOKEN'),
    ],

    'tracking' => [
        'meta_pixel_id' => env('META_PIXEL_ID'),
        'meta_capi_token' => env('META_CAPI_TOKEN'),
        'tiktok_pixel_id' => env('TIKTOK_PIXEL_ID'),
        'ga4_id' => env('GA4_ID'),
    ],
];
