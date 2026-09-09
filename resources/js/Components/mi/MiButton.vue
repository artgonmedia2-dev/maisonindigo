<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * Bouton de la maison. Un seul « primary » (indigo plein) par écran ;
 * les autres en « outline » (indigo) ou « ghost » (stone).
 */
type Variant = 'primary' | 'outline' | 'ghost';
type Size = 'md' | 'sm';

const props = withDefaults(
    defineProps<{
        variant?: Variant;
        size?: Size;
        /** Rend un lien Inertia au lieu d'un bouton. */
        href?: string;
        /** Lien externe (balise <a> classique). */
        external?: boolean;
        type?: 'button' | 'submit' | 'reset';
        disabled?: boolean;
        block?: boolean;
    }>(),
    {
        variant: 'outline',
        size: 'md',
        href: undefined,
        external: false,
        type: 'button',
        disabled: false,
        block: false,
    },
);

const variantClass: Record<Variant, string> = {
    primary:
        'bg-mi-indigo text-mi-blanc border-mi-indigo hover:bg-mi-indigo-deep hover:border-mi-indigo-deep',
    outline:
        'bg-transparent text-mi-indigo border-mi-indigo hover:bg-mi-blanc',
    ghost: 'bg-transparent text-mi-stone border-transparent px-0 hover:text-mi-indigo underline-offset-4 hover:underline',
};

const sizeClass: Record<Size, string> = {
    md: 'px-6 py-3.5 text-[15px]',
    sm: 'px-4 py-2.5 text-small',
};

const classes = computed(() => [
    'inline-flex items-center justify-center gap-2.5 border-2 font-body font-semibold leading-none transition-colors duration-150 disabled:cursor-not-allowed disabled:opacity-50',
    variantClass[props.variant],
    props.variant === 'ghost' ? (props.size === 'sm' ? 'py-2.5 text-small' : 'py-3.5 text-[15px]') : sizeClass[props.size],
    props.block ? 'flex w-full' : '',
]);
</script>

<template>
    <a v-if="href && external" :href="href" :class="classes" rel="noopener">
        <slot />
    </a>
    <Link v-else-if="href" :href="href" :class="classes">
        <slot />
    </Link>
    <button v-else :type="type" :disabled="disabled" :class="classes">
        <slot />
    </button>
</template>
