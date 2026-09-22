<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#1B2A4A">
        <meta name="geo.region" content="MA">
        <meta name="geo.placename" content="{{ __('seo.city') }}">
        <link rel="sitemap" type="application/xml" title="Sitemap" href="{{ url('/sitemap.xml') }}">

        <title inertia>{{ $page['props']['seo']['title'] ?? config('app.name', 'Maison Indigo') }}</title>

        @include('partials.seo')


        <link rel="preload" href="/fonts/cormorant-garamond-latin.woff2" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="/fonts/inter-latin.woff2" as="font" type="font/woff2" crossorigin>

        @routes
        @vite(['resources/js/app.ts', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-body bg-mi-ecru text-mi-charbon antialiased overflow-x-hidden">
        @inertia
    </body>
</html>
