{{--
    Métadonnées et JSON-LD rendus côté serveur.

    Le SSR Inertia est désactivé sur l'hébergement mutualisé : un robot ou un
    moteur génératif qui ne lit pas le JavaScript doit trouver le title, la
    canonical, les Open Graph et les schemas dans le HTML brut. Ils viennent
    donc de la prop `seo` construite par App\Services\SeoService, jamais de Vue.
--}}
@php
    /** @var array<string, mixed> $seo */
    $seo = $page['props']['seo'] ?? [];
    $og = $seo['og'] ?? [];
    $schemas = $seo['schemas'] ?? [];
    $encode = static fn (array $schema): string => json_encode(
        $schema,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
    );

    $defaultOgImage = app(App\Services\SeoService::class)->defaultOgImage();
    $ogImage = ! empty($og['image']) ? $og['image'] : $defaultOgImage;

    if (str_starts_with($ogImage, 'http://localhost')) {
        $ogImage = (string) preg_replace('#^http://localhost(:\d+)?#', 'https://maisonindigo.shop', $ogImage);
    } elseif (! str_starts_with($ogImage, 'http://') && ! str_starts_with($ogImage, 'https://')) {
        $base = str_starts_with((string) config('app.url'), 'https://')
            ? rtrim((string) config('app.url'), '/')
            : 'https://maisonindigo.shop';
        $ogImage = $base.'/'.ltrim($ogImage, '/');
    }

    $isDefaultImage = ($ogImage === $defaultOgImage || str_contains($ogImage, 'og-image'));
@endphp

<meta name="robots" content="{{ $seo['robots'] ?? __('seo.robots.index') }}">

@isset($seo['description'])
    <meta name="description" content="{{ $seo['description'] }}">
@endisset

@isset($seo['canonical'])
    <link rel="canonical" href="{{ $seo['canonical'] }}">
@endisset

<meta property="og:site_name" content="{{ __('seo.brand') }}">
<meta property="og:locale" content="fr_MA">
<meta property="og:type" content="{{ $og['type'] ?? 'website' }}">
<meta property="og:title" content="{{ $og['title'] ?? ($seo['title'] ?? config('app.name', 'Maison Indigo')) }}">
<meta property="og:description" content="{{ $og['description'] ?? ($seo['description'] ?? __('seo.entity')) }}">
<meta property="og:url" content="{{ $og['url'] ?? ($seo['canonical'] ?? url()->current()) }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:width" content="{{ $isDefaultImage ? '1200' : '800' }}">
<meta property="og:image:height" content="{{ $isDefaultImage ? '630' : '1000' }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:image" content="{{ $ogImage }}">

<script type="application/ld+json">{!! $encode(app(App\Services\SeoService::class)->organization()) !!}</script>

@foreach ($schemas as $schema)
    <script type="application/ld+json">{!! $encode($schema) !!}</script>
@endforeach
