<script setup lang="ts">
/**
 * Icônes de l'interface, tracées à la main en 24 px, trait 1.5.
 */
export type MiIconName =
    | 'search'
    | 'account'
    | 'cart'
    | 'menu'
    | 'close'
    | 'box'
    | 'exchange'
    | 'cash'
    | 'arrow';

withDefaults(
    defineProps<{
        name: MiIconName;
        size?: number;
    }>(),
    { size: 22 },
);
</script>

<template>
    <svg
        :width="size"
        :height="size"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.5"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
        focusable="false"
    >
        <template v-if="name === 'search'">
            <circle cx="11" cy="11" r="7" />
            <path d="m20 20-3.8-3.8" />
        </template>
        <template v-else-if="name === 'account'">
            <circle cx="12" cy="8" r="4" />
            <path d="M4.5 20.5a7.5 7.5 0 0 1 15 0" />
        </template>
        <template v-else-if="name === 'cart'">
            <path d="M4 4h2l1.2 2M7.2 6H20l-2 8H8.7L7.2 6Z" />
            <path d="M8.7 14 8 17h11" />
            <circle cx="9.5" cy="20.5" r="1" />
            <circle cx="17.5" cy="20.5" r="1" />
        </template>
        <template v-else-if="name === 'menu'">
            <path d="M4 7h16M4 12h16M4 17h16" />
        </template>
        <template v-else-if="name === 'close'">
            <path d="m6 6 12 12M18 6 6 18" />
        </template>
        <template v-else-if="name === 'box'">
            <path d="M3.5 8 12 3.5 20.5 8v9L12 21.5 3.5 17V8Z" />
            <path d="M3.5 8 12 12.5 20.5 8M12 12.5v9" />
        </template>
        <template v-else-if="name === 'exchange'">
            <path d="M4 8h13l-3-3M20 16H7l3 3" />
        </template>
        <template v-else-if="name === 'cash'">
            <rect x="3" y="6.5" width="18" height="11" />
            <circle cx="12" cy="12" r="2.5" />
            <path d="M6.5 9.5v5M17.5 9.5v5" />
        </template>
        <template v-else-if="name === 'arrow'">
            <path d="M5 12h14M13 6l6 6-6 6" />
        </template>
    </svg>
</template>
