<script setup lang="ts">
import MiIcon from '@/Components/mi/MiIcon.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * Bouton de la maison. Un seul « primary » (indigo plein) par écran ;
 * les autres en « outline » (indigo) ou « ghost » (stone).
 * `tone="ecru"` inverse les couleurs pour un fond indigo.
 */
type Variant = 'primary' | 'outline' | 'ghost';
type Tone = 'indigo' | 'ecru';
type Size = 'md' | 'sm';

const props = withDefaults(
    defineProps<{
        variant?: Variant;
        tone?: Tone;
        size?: Size;
        /** Rend un lien Inertia au lieu d'un bouton. */
        href?: string;
        /** Lien externe (balise <a> classique, nouvel onglet). */
        external?: boolean;
        type?: 'button' | 'submit' | 'reset';
        disabled?: boolean;
        /** État d'envoi : bouton désactivé, texte atténué. */
        loading?: boolean;
        block?: boolean;
        /** Flèche en fin de libellé, qui avance de 2 px au survol. */
        arrow?: boolean;
    }>(),
    {
        variant: 'outline',
        tone: 'indigo',
        size: 'md',
        href: undefined,
        external: false,
        type: 'button',
        disabled: false,
        loading: false,
        block: false,
        arrow: false,
    },
);

const variantClass: Record<Tone, Record<Variant, string>> = {
    indigo: {
        primary: 'bg-mi-indigo text-mi-blanc border-mi-indigo hover:bg-mi-indigo-deep hover:border-mi-indigo-deep',
        outline: 'bg-transparent text-mi-indigo border-mi-indigo hover:bg-mi-indigo hover:text-mi-blanc',
        ghost: 'bg-transparent text-mi-stone border-transparent px-0 hover:text-mi-indigo',
    },
    ecru: {
        primary: 'bg-mi-ecru text-mi-indigo border-mi-ecru hover:bg-mi-blanc hover:border-mi-blanc',
        outline: 'bg-transparent text-mi-ecru border-mi-ecru hover:bg-mi-ecru hover:text-mi-indigo',
        ghost: 'bg-transparent text-mi-ciel border-transparent px-0 hover:text-mi-ecru',
    },
};

const sizeClass: Record<Size, string> = {
    md: 'px-6 py-3.5 text-[15px]',
    sm: 'px-4 py-2.5 text-small',
};

const ghostSizeClass: Record<Size, string> = {
    md: 'py-3.5 text-[15px]',
    sm: 'py-2.5 text-small',
};

const arrowClass =
    'transition-transform duration-150 group-hover/btn:translate-x-0.5 rtl:group-hover/btn:-translate-x-0.5';

const isDisabled = computed(() => props.disabled || props.loading);

const classes = computed(() => [
    'group/btn inline-flex items-center justify-center gap-2.5 border-2 font-body font-semibold leading-none transition-colors duration-150',
    'disabled:cursor-not-allowed disabled:opacity-50 aria-disabled:cursor-not-allowed aria-disabled:opacity-50',
    variantClass[props.tone][props.variant],
    props.variant === 'ghost' ? ghostSizeClass[props.size] : sizeClass[props.size],
    props.block ? 'flex w-full' : '',
]);
</script>

<template>
    <a
        v-if="href && external"
        :href="href"
        :class="classes"
        :aria-disabled="isDisabled ? 'true' : undefined"
        rel="noopener"
        target="_blank"
    >
        <slot />
        <MiIcon v-if="arrow" name="arrow" :size="18" :class="arrowClass" />
    </a>
    <Link
        v-else-if="href"
        :href="href"
        :class="classes"
        :aria-disabled="isDisabled ? 'true' : undefined"
        :tabindex="isDisabled ? -1 : undefined"
    >
        <slot />
        <MiIcon v-if="arrow" name="arrow" :size="18" :class="arrowClass" />
    </Link>
    <button v-else :type="type" :disabled="isDisabled" :aria-busy="loading ? 'true' : undefined" :class="classes">
        <slot />
        <MiIcon v-if="arrow" name="arrow" :size="18" :class="arrowClass" />
    </button>
</template>
