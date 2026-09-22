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

    /*
    | Hébergement. TRUSTED_PROXIES : « * » derrière un proxy (Hostinger LiteSpeed, Cloudflare),
    | vide en local. FORCE_HTTPS : génère toutes les URL en https.
    */
    'trusted_proxies' => env('TRUSTED_PROXIES'),
    'force_https' => (bool) env('FORCE_HTTPS', false),

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

    'bank' => [
        'holder' => env('BANK_HOLDER'),
        'name' => env('BANK_NAME'),
        'iban' => env('BANK_IBAN'),
    ],

    'whatsapp' => [
        'token' => env('WHATSAPP_TOKEN'),
        'phone_id' => env('WHATSAPP_PHONE_ID'),
        'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),
        'app_secret' => env('WHATSAPP_APP_SECRET'),
    ],

    'telegram' => [
        'token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_CHAT_ID'),
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
