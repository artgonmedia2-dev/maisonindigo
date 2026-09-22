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
@isset($og['type'])<meta property="og:type" content="{{ $og['type'] }}">@endisset
@isset($og['title'])<meta property="og:title" content="{{ $og['title'] }}">@endisset
@isset($og['description'])<meta property="og:description" content="{{ $og['description'] }}">@endisset
@isset($og['url'])<meta property="og:url" content="{{ $og['url'] }}">@endisset
@if (! empty($og['image']))
    <meta property="og:image" content="{{ $og['image'] }}">
    <meta name="twitter:card" content="summary_large_image">
@else
    <meta name="twitter:card" content="summary">
@endif

<script type="application/ld+json">{!! $encode(app(App\Services\SeoService::class)->organization()) !!}</script>

@foreach ($schemas as $schema)
    <script type="application/ld+json">{!! $encode($schema) !!}</script>
@endforeach
