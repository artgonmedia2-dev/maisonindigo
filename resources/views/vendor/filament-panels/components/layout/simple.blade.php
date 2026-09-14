@props([
    'after' => null,
    'heading' => null,
    'subheading' => null,
])

@php
    use Filament\Livewire\SimpleUserMenu;
    use Filament\Support\Enums\Width;
    use Filament\Support\Facades\FilamentView;
    use Filament\View\PanelsRenderHook;

    $livewire ??= null;

    $renderHookScopes = $livewire?->getRenderHookScopes();
    $maxContentWidth ??= (filament()->getSimplePageMaxContentWidth() ?? Width::Large);

    if (is_string($maxContentWidth)) {
        $maxContentWidth = Width::tryFrom($maxContentWidth) ?? $maxContentWidth;
    }

    // Détecter si on est sur la page de connexion
    $isLoginPage = $livewire && $livewire instanceof \Filament\Auth\Pages\Login;
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    @if ($isLoginPage)
        {{-- ═══════════════════════════════════════════════════════════
             LAYOUT SPLIT-SCREEN MAISON INDIGO — Page de connexion
        ═══════════════════════════════════════════════════════════ --}}
        <div class="mi-login-wrap">

            {{-- ── PANNEAU GAUCHE — Hero illustration ── --}}
            <aside class="mi-login-hero" aria-hidden="true">
                <img
                    src="{{ asset('images/admin-login-hero.jpg') }}"
                    alt=""
                    class="mi-login-hero__bg"
                    loading="eager"
                >
                <div class="mi-login-hero__overlay"></div>
                <div class="mi-login-hero__grain"></div>

                <div class="mi-login-hero__content">
                    {{-- Wordmark --}}
                    <div class="mi-login-hero__wordmark">
                        <span class="mi-login-hero__wordmark-maison">Maison</span>
                        <span class="mi-login-hero__wordmark-stitch" aria-hidden="true"></span>
                        <span class="mi-login-hero__wordmark-indigo">Indigo</span>
                    </div>

                    {{-- Citation éditoriale --}}
                    <blockquote class="mi-login-hero__quote">
                        <p>L'élégance est le reflet<br>de la précision artisanale.</p>
                        <footer>— Atelier Maison Indigo</footer>
                    </blockquote>

                    {{-- Pied décoratif --}}
                    <div class="mi-login-hero__footer">
                        <span class="mi-login-hero__footer-line"></span>
                        <span class="mi-login-hero__footer-tag">Back-office · v{{ config('app.version', '1.0') }}</span>
                    </div>
                </div>
            </aside>

            {{-- ── PANNEAU DROIT — Formulaire ── --}}
            <div class="mi-login-panel">
                {{-- Texture de fond --}}
                <div class="mi-login-panel__bg" aria-hidden="true"></div>

                {{-- Wordmark mobile --}}
                <div class="mi-login-panel__mobile-brand">
                    <span class="mi-brand">
                        <span class="mi-brand__maison">Maison</span>
                        <span class="mi-brand__stitch" aria-hidden="true"></span>
                        <span class="mi-brand__indigo">Indigo</span>
                    </span>
                </div>

                {{-- Carte formulaire --}}
                <div class="mi-login-card">
                    {{-- Coins décoratifs --}}
                    <div class="mi-login-card__corner mi-login-card__corner--tl" aria-hidden="true"></div>
                    <div class="mi-login-card__corner mi-login-card__corner--br" aria-hidden="true"></div>

                    {{-- En-tête --}}
                    <header class="mi-login-card__header">
                        <p class="mi-login-card__kicker">Espace réservé</p>
                        <h1 class="mi-login-card__title">Connexion</h1>
                        <p class="mi-login-card__subtitle">Bienvenue dans votre espace d'administration.</p>
                    </header>

                    {{-- Ornement --}}
                    <div class="mi-login-card__ornament" aria-hidden="true">
                        <span class="mi-login-card__ornament-line"></span>
                        <svg viewBox="0 0 14 14" fill="currentColor" class="mi-login-card__ornament-gem">
                            <path d="M7 1L8 5H12L9 7.5L10 12L7 9.5L4 12L5 7.5L2 5H6L7 1Z"/>
                        </svg>
                        <span class="mi-login-card__ornament-line"></span>
                    </div>

                    {{-- Contenu formulaire injecté par Filament --}}
                    <div class="mi-login-card__form">
                        {{ $slot }}
                    </div>

                    {{-- Badge sécurité --}}
                    <div class="mi-login-card__security">
                        <svg viewBox="0 0 16 16" fill="none" class="mi-login-card__security-icon">
                            <path d="M8 1.5L14 4V9.5C14 12.5 11.5 14.5 8 15C4.5 14.5 2 12.5 2 9.5V4L8 1.5Z"
                                  stroke="currentColor" stroke-width="1.25" stroke-linejoin="round"/>
                            <path d="M5.5 8L7 9.5L10.5 6" stroke="currentColor" stroke-width="1.25"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Connexion sécurisée · Session chiffrée</span>
                    </div>
                </div>

                {{-- Pied de page --}}
                <footer class="mi-login-panel__footer">
                    &copy; {{ date('Y') }} Maison Indigo — Tous droits réservés
                </footer>
            </div>
        </div>

        {{-- ── STYLES ── --}}
        @once
        <style>
        /* ============================================================
           VARIABLES
        ============================================================ */
        :root {
            --mi-indigo:    #1b2a4a;
            --mi-indigo-deep: #0f1b33;
            --mi-ecru:      #f4efe6;
            --mi-ocre:      #d9822b;
            --mi-fil:       #8a8f98;
            --mi-ligne:     #e3ded3;
            --mi-ciel:      #b9c4d9;
            --mi-vert:      #2e7d5b;
        }

        /* ============================================================
           LAYOUT SPLIT-SCREEN
        ============================================================ */
        .mi-login-wrap {
            display: grid;
            grid-template-columns: 1fr;
            min-height: 100dvh;
            font-family: 'Inter', system-ui, sans-serif;
        }

        @media (min-width: 64rem) {
            .mi-login-wrap {
                grid-template-columns: 42% 58%;
            }
        }

        /* ============================================================
           PANNEAU GAUCHE — HERO
        ============================================================ */
        .mi-login-hero {
            display: none;
            position: relative;
            overflow: hidden;
            background-color: var(--mi-indigo-deep);
        }

        @media (min-width: 64rem) {
            .mi-login-hero {
                display: flex;
                flex-direction: column;
                justify-content: flex-end;
            }
        }

        .mi-login-hero__bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center 20%;
            opacity: 0.5;
        }

        .mi-login-hero__overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                175deg,
                rgba(15, 27, 51, 0.15) 0%,
                rgba(15, 27, 51, 0.5) 45%,
                rgba(15, 27, 51, 0.95) 100%
            );
        }

        .mi-login-hero__grain {
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
            opacity: 0.5;
            mix-blend-mode: overlay;
            pointer-events: none;
        }

        .mi-login-hero__content {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            height: 100%;
            padding: 3rem;
            gap: 2rem;
        }

        /* Wordmark hero */
        .mi-login-hero__wordmark {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-weight: 600;
            color: var(--mi-ecru);
            line-height: 0.95;
            margin-top: 0;
            margin-bottom: auto;
        }

        .mi-login-hero__wordmark-maison {
            font-size: 0.75rem;
            letter-spacing: 0.32em;
            font-weight: 500;
            text-transform: uppercase;
            color: var(--mi-ciel);
        }

        .mi-login-hero__wordmark-stitch {
            display: block;
            width: 100%;
            border-top: 1.5px dashed var(--mi-ocre);
            margin-block: 0.4rem;
        }

        .mi-login-hero__wordmark-indigo {
            font-size: 2.5rem;
            letter-spacing: 0.06em;
        }

        /* Citation */
        .mi-login-hero__quote {
            margin: 0;
            border-inline-start: 2px solid var(--mi-ocre);
            padding-inline-start: 1.25rem;
        }

        .mi-login-hero__quote p {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 1.375rem;
            line-height: 1.45;
            font-weight: 500;
            font-style: italic;
            color: var(--mi-ecru);
            margin: 0 0 0.5rem;
        }

        .mi-login-hero__quote footer {
            font-size: 0.6875rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--mi-fil);
            font-style: normal;
        }

        /* Pied hero */
        .mi-login-hero__footer {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .mi-login-hero__footer-line {
            flex: 1;
            height: 1px;
            background: rgba(255,255,255,0.12);
        }

        .mi-login-hero__footer-tag {
            font-size: 0.6875rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--mi-ciel);
            opacity: 0.7;
        }

        /* ============================================================
           PANNEAU DROIT — FORMULAIRE
        ============================================================ */
        .mi-login-panel {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100dvh;
            background-color: var(--mi-ecru);
            padding: 2.5rem 1.5rem;
            gap: 1.5rem;
        }

        .mi-login-panel__bg {
            position: absolute;
            inset: 0;
            background-image:
                repeating-linear-gradient(90deg, rgba(27,42,74,0.03) 0 1px, transparent 1px 60px),
                repeating-linear-gradient(0deg, rgba(27,42,74,0.03) 0 1px, transparent 1px 60px);
            pointer-events: none;
        }

        /* Wordmark mobile */
        .mi-login-panel__mobile-brand {
            position: relative;
            display: flex;
            justify-content: center;
        }

        @media (min-width: 64rem) {
            .mi-login-panel__mobile-brand {
                display: none;
            }
        }

        /* Carte */
        .mi-login-card {
            position: relative;
            width: 100%;
            max-width: 27rem;
            background: #ffffff;
            border: 1px solid var(--mi-ligne);
            padding: 2.5rem 2.25rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        /* Coins décoratifs */
        .mi-login-card__corner {
            position: absolute;
            width: 3rem;
            height: 3rem;
            pointer-events: none;
        }

        .mi-login-card__corner--tl {
            top: 0;
            right: 0;
            border-top: 2.5px solid var(--mi-ocre);
            border-right: 2.5px solid var(--mi-ocre);
        }

        .mi-login-card__corner--br {
            bottom: 0;
            left: 0;
            border-bottom: 2.5px solid var(--mi-ocre);
            border-left: 2.5px solid var(--mi-ocre);
        }

        /* En-tête carte */
        .mi-login-card__kicker {
            font-size: 0.6875rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            font-weight: 600;
            color: var(--mi-fil);
            margin: 0;
        }

        .mi-login-card__title {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 2.5rem;
            font-weight: 600;
            line-height: 1;
            color: var(--mi-indigo);
            margin: 0.25rem 0 0;
            letter-spacing: -0.01em;
        }

        .mi-login-card__subtitle {
            font-size: 0.875rem;
            color: var(--mi-fil);
            margin: 0;
            line-height: 1.5;
        }

        /* Ornement */
        .mi-login-card__ornament {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--mi-ocre);
        }

        .mi-login-card__ornament-line {
            flex: 1;
            height: 1px;
            background: var(--mi-ligne);
        }

        .mi-login-card__ornament-gem {
            width: 0.875rem;
            height: 0.875rem;
            flex-shrink: 0;
        }

        /* Override champs Filament dans la carte login */
        .mi-login-card .fi-fo-field-label-content {
            font-size: 0.6875rem !important;
            letter-spacing: 0.12em !important;
            text-transform: uppercase !important;
            font-weight: 600 !important;
            color: var(--mi-indigo) !important;
        }

        .mi-login-card .fi-input-wrp {
            background: #fafaf9 !important;
            border: 1.5px solid var(--mi-ligne) !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            transition: border-color 180ms !important;
        }

        .mi-login-card .fi-input-wrp:focus-within {
            border-color: var(--mi-indigo) !important;
            background: #fff !important;
        }

        .mi-login-card .fi-input {
            background: transparent !important;
        }

        /* Override bouton submit */
        .mi-login-card .fi-btn[type="submit"],
        .mi-login-card .fi-btn-color-primary {
            width: 100% !important;
            background: var(--mi-indigo) !important;
            color: var(--mi-ecru) !important;
            border: none !important;
            border-radius: 0 !important;
            padding: 0.875rem 1.5rem !important;
            font-size: 0.75rem !important;
            letter-spacing: 0.18em !important;
            text-transform: uppercase !important;
            font-weight: 600 !important;
            transition: background-color 180ms, transform 100ms !important;
            justify-content: center !important;
            box-shadow: none !important;
        }

        .mi-login-card .fi-btn[type="submit"]:hover,
        .mi-login-card .fi-btn-color-primary:hover {
            background: var(--mi-indigo-deep) !important;
            transform: translateY(-1px);
        }

        /* Liens (mot de passe oublié) */
        .mi-login-card a:not(.fi-btn) {
            color: var(--mi-indigo);
            text-decoration: none;
            font-size: 0.8125rem;
            border-bottom: 1px solid transparent;
            transition: border-color 150ms, color 150ms;
        }

        .mi-login-card a:not(.fi-btn):hover {
            color: var(--mi-indigo-deep);
            border-bottom-color: var(--mi-ocre);
        }

        /* Badge sécurité */
        .mi-login-card__security {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.6875rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--mi-fil);
            padding-top: 0.25rem;
            border-top: 1px solid var(--mi-ligne);
        }

        .mi-login-card__security-icon {
            width: 0.875rem;
            height: 0.875rem;
            color: var(--mi-vert);
            flex-shrink: 0;
        }

        /* Pied de page panel */
        .mi-login-panel__footer {
            position: relative;
            font-size: 0.6875rem;
            letter-spacing: 0.08em;
            color: var(--mi-fil);
            text-align: center;
        }

        /* ============================================================
           ANIMATIONS D'ENTRÉE
        ============================================================ */
        @media (prefers-reduced-motion: no-preference) {
            .mi-login-card {
                animation: mi-card-in 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
            }

            @keyframes mi-card-in {
                from { opacity: 0; transform: translateY(1.25rem); }
                to   { opacity: 1; transform: translateY(0); }
            }

            .mi-login-hero__bg {
                animation: mi-hero-in 1.4s ease both;
            }

            @keyframes mi-hero-in {
                from { opacity: 0; transform: scale(1.04); }
                to   { opacity: 0.5; transform: scale(1); }
            }
        }
        </style>
        @endonce

    @else
        {{-- ─── LAYOUT STANDARD FILAMENT (non-login pages) ─── --}}
        <div class="fi-simple-layout">
            @if (($hasTopbar ?? true) && filament()->auth()->check())
                <a href="#fi-main-content" class="fi-skip-link fi-sr-only">
                    {{ __('filament-panels::layout.skip_to_content.label') }}
                </a>
            @endif

            {{ FilamentView::renderHook(PanelsRenderHook::SIMPLE_LAYOUT_START, scopes: $renderHookScopes) }}

            @if (($hasTopbar ?? true) && filament()->auth()->check())
                <div class="fi-simple-layout-header">
                    @if (filament()->hasDatabaseNotifications())
                        @livewire(filament()->getDatabaseNotificationsLivewireComponent(), [
                            'lazy' => filament()->hasLazyLoadedDatabaseNotifications(),
                            'position' => \Filament\Enums\DatabaseNotificationsPosition::Topbar,
                        ])
                    @endif

                    @if (filament()->hasUserMenu())
                        @livewire(SimpleUserMenu::class)
                    @endif
                </div>
            @endif

            <div class="fi-simple-main-ctn">
                <main
                    id="fi-main-content"
                    tabindex="-1"
                    @class([
                        'fi-simple-main',
                        ($maxContentWidth instanceof Width) ? "fi-width-{$maxContentWidth->value}" : $maxContentWidth,
                    ])
                >
                    {{ $slot }}
                </main>
            </div>

            {{ FilamentView::renderHook(PanelsRenderHook::FOOTER, scopes: $renderHookScopes) }}

            {{ FilamentView::renderHook(PanelsRenderHook::SIMPLE_LAYOUT_END, scopes: $renderHookScopes) }}
        </div>
    @endif
</x-filament-panels::layout.base>
