<script setup lang="ts">
import { computed } from 'vue';

/**
 * Logo de la maison.
 * - wordmark : « Maison » en petites capitales espacées, surpiqûre ocre, « Indigo » en grand.
 * - monogram : MI dans un carré aux coins coupés, comme la patte cuir d'un jean.
 * - auto : monogramme sous 768 px, wordmark au-delà.
 *
 * En monochrome (charbon, blanc), la surpiqûre prend la couleur du texte.
 */
type Variant = 'wordmark' | 'monogram' | 'auto';
type Tone = 'indigo' | 'ecru' | 'charbon' | 'blanc';

const props = withDefaults(
    defineProps<{
        variant?: Variant;
        tone?: Tone;
        /** Largeur du wordmark en px (hauteur du monogramme). */
        width?: number;
        title?: string;
    }>(),
    {
        variant: 'wordmark',
        tone: 'indigo',
        width: 128,
        title: 'Maison Indigo',
    },
);

const toneClass: Record<Tone, string> = {
    indigo: 'text-mi-indigo',
    ecru: 'text-mi-ecru',
    charbon: 'text-mi-charbon',
    blanc: 'text-mi-blanc',
};

const monochrome = computed(() => props.tone === 'charbon' || props.tone === 'blanc');
const stitchColor = computed(() => (monochrome.value ? 'currentColor' : '#D9822B'));

/** Fond du monogramme : le ton ; texte : la couleur qui contraste. */
const monogramFill = computed(() => {
    switch (props.tone) {
        case 'indigo':
            return { background: '#1B2A4A', text: '#F4EFE6' };
        case 'ecru':
            return { background: '#F4EFE6', text: '#1B2A4A' };
        case 'charbon':
            return { background: '#141414', text: '#FFFFFF' };
        case 'blanc':
            return { background: '#FFFFFF', text: '#141414' };
    }
});

const wordmarkHeight = computed(() => Math.round(props.width * 0.5));
const monogramSize = computed(() => Math.round(props.width * 0.375));

const showWordmark = computed(() => props.variant !== 'monogram');
const showMonogram = computed(() => props.variant !== 'wordmark');
</script>

<template>
    <span class="inline-flex items-center" :class="toneClass[tone]">
        <!-- Wordmark deux lignes -->
        <svg
            v-if="showWordmark"
            :class="variant === 'auto' ? 'hidden md:block' : ''"
            :width="width"
            :height="wordmarkHeight"
            viewBox="0 0 200 100"
            overflow="visible"
            role="img"
            :aria-label="title"
        >
            <title>{{ title }}</title>
            <text
                x="1"
                y="20"
                font-family="Cormorant Garamond, Georgia, serif"
                font-weight="500"
                font-size="17"
                letter-spacing="5.6"
                fill="currentColor"
            >
                MAISON
            </text>
            <line
                x1="0"
                y1="30"
                x2="196"
                y2="30"
                :stroke="stitchColor"
                stroke-width="2"
                stroke-dasharray="7 4"
            />
            <text
                x="0"
                y="88"
                font-family="Cormorant Garamond, Georgia, serif"
                font-weight="600"
                font-size="66"
                letter-spacing="3.6"
                fill="currentColor"
            >
                Indigo
            </text>
        </svg>

        <!-- Monogramme MI -->
        <svg
            v-if="showMonogram"
            :class="variant === 'auto' ? 'md:hidden' : ''"
            :width="monogramSize"
            :height="monogramSize"
            viewBox="0 0 64 64"
            role="img"
            :aria-label="title"
        >
            <title>{{ title }}</title>
            <path d="M8 0h56v56l-8 8H0V8z" :fill="monogramFill.background" />
            <path
                d="M10.5 5.5h48v46l-5 5h-48v-46z"
                fill="none"
                :stroke="monochrome ? monogramFill.text : '#D9822B'"
                stroke-width="1.5"
                stroke-dasharray="3 2.5"
            />
            <text
                x="32"
                y="43"
                text-anchor="middle"
                font-family="Cormorant Garamond, Georgia, serif"
                font-weight="600"
                font-size="30"
                letter-spacing="-1"
                :fill="monogramFill.text"
            >
                MI
            </text>
        </svg>
    </span>
</template>
