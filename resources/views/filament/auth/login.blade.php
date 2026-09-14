{{--
    Page de connexion — Maison Indigo · Back-office
    Design : panneau de gauche illustration + panneau de droite formulaire
--}}
<x-filament-panels::page.simple>
    {{-- ─── HEAD SCRIPTS / STYLES LOCAUX ─────────────────────────── --}}
    @push('styles')
    <style>
        /* Reset du layout simple de Filament pour notre split-screen */
        .fi-simple-layout {
            display: block !important;
            padding: 0 !important;
            min-height: 100dvh;
            background: #0f1b33;
        }
        .fi-simple-main {
            border: none !important;
            box-shadow: none !important;
            background: transparent !important;
            padding: 0 !important;
            max-width: 100% !important;
            width: 100% !important;
        }
        /* Fond animé en cas de JS désactivé */
        @keyframes mi-grain {
            0%   { transform: translate(0, 0); }
            20%  { transform: translate(-2%, -2%); }
            40%  { transform: translate(2%, -1%); }
            60%  { transform: translate(-1%,  2%); }
            80%  { transform: translate(2%,  1%); }
            100% { transform: translate(0, 0); }
        }
    </style>
    @endpush

    {{-- ─── WRAPPER SPLIT-SCREEN ───────────────────────────────────── --}}
    <div class="mi-login">

        {{-- ══════════════════════════════════════════
             PANNEAU GAUCHE — illustration & branding
        ══════════════════════════════════════════ --}}
        <aside class="mi-login__hero" aria-hidden="true">

            {{-- Photo de fond --}}
            <img
                src="{{ asset('images/admin-login-hero.jpg') }}"
                alt=""
                class="mi-login__hero-bg"
                loading="eager"
            >

            {{-- Dégradé d'assombrissement --}}
            <div class="mi-login__hero-overlay"></div>

            {{-- Contenu flottant --}}
            <div class="mi-login__hero-content">

                {{-- Wordmark --}}
                <div class="mi-login__wordmark">
                    <span class="mi-login__wordmark-maison">Maison</span>
                    <span class="mi-login__wordmark-stitch" aria-hidden="true"></span>
                    <span class="mi-login__wordmark-indigo">Indigo</span>
                </div>

                {{-- Texte éditorial --}}
                <blockquote class="mi-login__quote">
                    <p>L'élégance est le reflet<br>de la précision artisanale.</p>
                    <footer class="mi-login__quote-src">— Atelier Maison Indigo</footer>
                </blockquote>

                {{-- Détail décoratif bas --}}
                <div class="mi-login__hero-footer">
                    <span class="mi-login__hero-line"></span>
                    <span class="mi-login__hero-tag">Back-office · v{{ config('app.version', '1.0') }}</span>
                </div>
            </div>

            {{-- Grain animé --}}
            <div class="mi-login__hero-grain" aria-hidden="true"></div>
        </aside>

        {{-- ══════════════════════════════════════════
             PANNEAU DROIT — formulaire
        ══════════════════════════════════════════ --}}
        <main class="mi-login__form-panel">

            {{-- Header mobile : wordmark visible uniquement sur petit écran --}}
            <div class="mi-login__mobile-brand" aria-hidden="true">
                <span class="mi-brand">
                    <span class="mi-brand__maison">Maison</span>
                    <span class="mi-brand__stitch"></span>
                    <span class="mi-brand__indigo">Indigo</span>
                </span>
            </div>

            {{-- Carte formulaire --}}
            <div class="mi-login__card">

                {{-- En-tête --}}
                <header class="mi-login__card-header">
                    <p class="mi-login__card-kicker">Espace réservé</p>
                    <h1 class="mi-login__card-title">Connexion</h1>
                    <p class="mi-login__card-subtitle">
                        Bienvenue dans votre espace d'administration.
                    </p>
                </header>

                {{-- Séparateur ornemental --}}
                <div class="mi-login__ornament" aria-hidden="true">
                    <span class="mi-login__ornament-line"></span>
                    <svg class="mi-login__ornament-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 1L9.5 6.5H15L10.5 9.5L12 15L8 12L4 15L5.5 9.5L1 6.5H6.5L8 1Z" fill="currentColor"/>
                    </svg>
                    <span class="mi-login__ornament-line"></span>
                </div>

                {{-- Formulaire Filament --}}
                <div class="mi-login__filament-form">
                    <x-filament-panels::form id="form" wire:submit="authenticate">
                        {{ $this->form }}

                        <x-filament::button
                            type="submit"
                            size="lg"
                            wire:loading.attr="disabled"
                            class="mi-login__submit-btn"
                            id="login-submit-btn"
                        >
                            <span wire:loading.remove wire:target="authenticate">
                                Se connecter
                            </span>
                            <span wire:loading wire:target="authenticate" class="mi-login__loading">
                                <svg class="mi-login__spinner" viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" stroke-dasharray="30" stroke-dashoffset="0"/>
                                </svg>
                                Connexion en cours…
                            </span>
                        </x-filament::button>
                    </x-filament-panels::form>
                </div>

                {{-- Lien mot de passe oublié --}}
                @if (filament()->hasPasswordReset())
                    <div class="mi-login__forgot">
                        <a
                            href="{{ filament()->getRequestPasswordResetUrl() }}"
                            class="mi-login__forgot-link"
                            id="forgot-password-link"
                        >
                            Mot de passe oublié ?
                        </a>
                    </div>
                @endif

                {{-- Sécu badge --}}
                <div class="mi-login__security">
                    <svg viewBox="0 0 16 16" fill="none" class="mi-login__security-icon">
                        <path d="M8 1.5L14 4V9.5C14 12.5 11.5 14.5 8 15C4.5 14.5 2 12.5 2 9.5V4L8 1.5Z"
                              stroke="currentColor" stroke-width="1.25" stroke-linejoin="round"/>
                        <path d="M5.5 8L7 9.5L10.5 6" stroke="currentColor" stroke-width="1.25"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Connexion sécurisée · Session chiffrée</span>
                </div>
            </div>

            {{-- Pied de page --}}
            <footer class="mi-login__form-footer">
                &copy; {{ date('Y') }} Maison Indigo — Tous droits réservés
            </footer>
        </main>
    </div>

    {{-- ─── STYLES INLINE ─────────────────────────────────────────── --}}
    <style>
    /* ================================================================
       LAYOUT SPLIT-SCREEN
    ================================================================ */
    .mi-login {
        display: grid;
        grid-template-columns: 1fr;
        min-height: 100dvh;
        font-family: 'Inter', system-ui, sans-serif;
    }

    @media (min-width: 64rem) {
        .mi-login {
            grid-template-columns: 45% 55%;
        }
    }

    /* ================================================================
       PANNEAU GAUCHE — HERO
    ================================================================ */
    .mi-login__hero {
        display: none;
        position: relative;
        overflow: hidden;
        background-color: #0f1b33;
    }

    @media (min-width: 64rem) {
        .mi-login__hero {
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }
    }

    .mi-login__hero-bg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center top;
        opacity: 0.55;
        transition: opacity 1s ease;
    }

    .mi-login__hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(
            180deg,
            rgba(15, 27, 51, 0.2) 0%,
            rgba(15, 27, 51, 0.55) 40%,
            rgba(15, 27, 51, 0.92) 100%
        );
    }

    .mi-login__hero-grain {
        position: absolute;
        inset: -20%;
        width: 140%;
        height: 140%;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.035'/%3E%3C/svg%3E");
        background-size: 180px 180px;
        opacity: 0.4;
        mix-blend-mode: overlay;
        pointer-events: none;
        animation: mi-grain 8s steps(2) infinite;
    }

    @keyframes mi-grain {
        0%   { transform: translate(0, 0); }
        25%  { transform: translate(-1%, 2%); }
        50%  { transform: translate(1%, -1%); }
        75%  { transform: translate(-2%, 1%); }
        100% { transform: translate(0, 0); }
    }

    .mi-login__hero-content {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        height: 100%;
        padding: 3rem;
        gap: 2rem;
    }

    /* Wordmark hero */
    .mi-login__wordmark {
        display: inline-flex;
        flex-direction: column;
        align-items: flex-start;
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-weight: 600;
        color: #f4efe6;
        line-height: 0.95;
        white-space: nowrap;
        margin-bottom: auto;
        padding-top: 3rem;
    }

    .mi-login__wordmark-maison {
        font-size: 0.75rem;
        letter-spacing: 0.32em;
        font-weight: 500;
        text-transform: uppercase;
        padding-inline-start: 0.06em;
        color: #b9c4d9;
    }

    .mi-login__wordmark-stitch {
        display: block;
        width: 100%;
        border-top: 1.5px dashed #d9822b;
        margin-block: 0.4rem;
    }

    .mi-login__wordmark-indigo {
        font-size: 2.5rem;
        letter-spacing: 0.06em;
    }

    /* Citation */
    .mi-login__quote {
        margin: 0;
        border-inline-start: 2px solid #d9822b;
        padding-inline-start: 1.25rem;
    }

    .mi-login__quote p {
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-size: 1.375rem;
        line-height: 1.4;
        font-weight: 500;
        font-style: italic;
        color: #f4efe6;
        margin: 0 0 0.5rem;
    }

    .mi-login__quote-src {
        font-size: 0.6875rem;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: #8a8f98;
        font-style: normal;
    }

    /* Pied hero */
    .mi-login__hero-footer {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .mi-login__hero-line {
        flex: 1;
        height: 1px;
        background: rgba(255,255,255,0.15);
    }

    .mi-login__hero-tag {
        font-size: 0.6875rem;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: #4f6d9a;
    }

    /* ================================================================
       PANNEAU DROIT — FORMULAIRE
    ================================================================ */
    .mi-login__form-panel {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 100dvh;
        background-color: #f4efe6;
        padding: 2rem 1.5rem;
        gap: 2rem;
        position: relative;
    }

    /* Texture subtile fond droit */
    .mi-login__form-panel::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image:
            repeating-linear-gradient(90deg, rgba(27,42,74,0.025) 0 1px, transparent 1px 60px),
            repeating-linear-gradient(0deg, rgba(27,42,74,0.025) 0 1px, transparent 1px 60px);
        pointer-events: none;
    }

    /* Wordmark mobile */
    .mi-login__mobile-brand {
        display: flex;
        justify-content: center;
    }

    @media (min-width: 64rem) {
        .mi-login__mobile-brand {
            display: none;
        }
    }

    /* Carte */
    .mi-login__card {
        position: relative;
        width: 100%;
        max-width: 28rem;
        background: #ffffff;
        border: 1px solid #e3ded3;
        padding: 2.5rem 2.25rem;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    /* Coin décoratif haut-droit */
    .mi-login__card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 3.5rem;
        height: 3.5rem;
        border-top: 3px solid #d9822b;
        border-right: 3px solid #d9822b;
        pointer-events: none;
    }

    /* Coin décoratif bas-gauche */
    .mi-login__card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 3.5rem;
        height: 3.5rem;
        border-bottom: 3px solid #d9822b;
        border-left: 3px solid #d9822b;
        pointer-events: none;
    }

    /* En-tête carte */
    .mi-login__card-kicker {
        font-size: 0.6875rem;
        letter-spacing: 0.22em;
        text-transform: uppercase;
        font-weight: 600;
        color: #8a8f98;
        margin: 0;
    }

    .mi-login__card-title {
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-size: 2.5rem;
        font-weight: 600;
        line-height: 1;
        color: #1b2a4a;
        margin: 0.25rem 0 0;
        letter-spacing: -0.01em;
    }

    .mi-login__card-subtitle {
        font-size: 0.875rem;
        color: #8a8f98;
        margin: 0;
        line-height: 1.5;
    }

    /* Ornement */
    .mi-login__ornament {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: #d9822b;
    }

    .mi-login__ornament-line {
        flex: 1;
        height: 1px;
        background: #e3ded3;
    }

    .mi-login__ornament-icon {
        width: 0.875rem;
        height: 0.875rem;
        flex-shrink: 0;
    }

    /* Formulaire Filament overrides dans ce contexte */
    .mi-login__filament-form .fi-fo-field-wrp {
        gap: 0.375rem;
    }

    .mi-login__filament-form .fi-input-wrp {
        background: #fafaf9 !important;
        border: 1.5px solid #e3ded3 !important;
        transition: border-color 200ms, background-color 200ms !important;
    }

    .mi-login__filament-form .fi-input-wrp:focus-within {
        border-color: #1b2a4a !important;
        background: #ffffff !important;
    }

    .mi-login__filament-form .fi-fo-field-label-content {
        font-size: 0.75rem !important;
        letter-spacing: 0.1em !important;
        text-transform: uppercase !important;
        font-weight: 600 !important;
        color: #4f6d9a !important;
    }

    /* Bouton submit */
    .mi-login__submit-btn,
    .mi-login__filament-form [type="submit"] {
        width: 100% !important;
        background: #1b2a4a !important;
        color: #f4efe6 !important;
        border: none !important;
        padding: 0.875rem 1.5rem !important;
        font-size: 0.8125rem !important;
        letter-spacing: 0.18em !important;
        text-transform: uppercase !important;
        font-weight: 600 !important;
        transition: background-color 200ms, transform 100ms !important;
        margin-top: 0.5rem;
    }

    .mi-login__submit-btn:hover {
        background: #0f1b33 !important;
        transform: translateY(-1px);
    }

    .mi-login__submit-btn:active {
        transform: translateY(0);
    }

    /* Loading spinner */
    .mi-login__loading {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .mi-login__spinner {
        width: 1rem;
        height: 1rem;
        animation: mi-spin 1s linear infinite;
    }

    .mi-login__spinner circle {
        stroke-dasharray: 30;
        stroke-dashoffset: 20;
        stroke-linecap: round;
    }

    @keyframes mi-spin {
        to { transform: rotate(360deg); }
    }

    /* Mot de passe oublié */
    .mi-login__forgot {
        text-align: center;
    }

    .mi-login__forgot-link {
        font-size: 0.8125rem;
        color: #4f6d9a;
        text-decoration: none;
        letter-spacing: 0.04em;
        border-bottom: 1px solid transparent;
        transition: color 150ms, border-color 150ms;
    }

    .mi-login__forgot-link:hover {
        color: #1b2a4a;
        border-bottom-color: #d9822b;
    }

    /* Badge sécurité */
    .mi-login__security {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-size: 0.6875rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #8a8f98;
        padding-top: 0.5rem;
        border-top: 1px solid #e3ded3;
    }

    .mi-login__security-icon {
        width: 0.875rem;
        height: 0.875rem;
        color: #2e7d5b;
        flex-shrink: 0;
    }

    /* Pied de page du panel */
    .mi-login__form-footer {
        font-size: 0.6875rem;
        letter-spacing: 0.08em;
        color: #8a8f98;
        text-align: center;
    }

    /* ================================================================
       ANIMATION D'ENTRÉE
    ================================================================ */
    @media (prefers-reduced-motion: no-preference) {
        .mi-login__card {
            animation: mi-card-in 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes mi-card-in {
            from {
                opacity: 0;
                transform: translateY(1.5rem);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .mi-login__hero-bg {
            animation: mi-hero-in 1.2s ease both;
        }

        @keyframes mi-hero-in {
            from { opacity: 0; transform: scale(1.05); }
            to   { opacity: 0.55; transform: scale(1); }
        }
    }
    </style>
</x-filament-panels::page.simple>
