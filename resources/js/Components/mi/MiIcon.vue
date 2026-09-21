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
    | 'arrow'
    | 'arrow-back'
    | 'chevron'
    | 'check'
    | 'mail'
    | 'chat'
    | 'pin'
    | 'ruler'
    | 'eye'
    | 'eye-off'
    | 'logout'
    | 'alert'
    | 'info'
    | 'ticket';

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
        <template v-else-if="name === 'arrow-back'">
            <path d="M19 12H5M11 6l-6 6 6 6" />
        </template>
        <template v-else-if="name === 'chevron'">
            <path d="m6 9 6 6 6-6" />
        </template>
        <template v-else-if="name === 'check'">
            <path d="m5 12.5 4.5 4.5L19 7" />
        </template>
        <template v-else-if="name === 'mail'">
            <rect x="3" y="5.5" width="18" height="13" />
            <path d="m3 7 9 6 9-6" />
        </template>
        <template v-else-if="name === 'chat'">
            <path d="M12 3.5a8.5 8.5 0 0 0-7.3 12.9L3.5 20.5l4.3-1.1A8.5 8.5 0 1 0 12 3.5Z" />
            <path d="M9 10.5c.3 1.9 2.6 4.2 4.5 4.5l1.3-1.3-1.8-1-1 .7c-.7-.4-1.3-1-1.7-1.7l.7-1-1-1.8L9 10.5Z" />
        </template>
        <template v-else-if="name === 'pin'">
            <path d="M12 21s-6.5-6-6.5-11a6.5 6.5 0 0 1 13 0c0 5-6.5 11-6.5 11Z" />
            <circle cx="12" cy="10" r="2.25" />
        </template>
        <template v-else-if="name === 'ruler'">
            <path d="m3.5 16.5 13-13 4 4-13 13z" />
            <path d="m7 13 1.5 1.5M9.5 10.5 11 12M12 8l1.5 1.5M14.5 5.5 16 7" />
        </template>
        <template v-else-if="name === 'eye'">
            <path d="M2.5 12s3.5-6.5 9.5-6.5S21.5 12 21.5 12s-3.5 6.5-9.5 6.5S2.5 12 2.5 12Z" />
            <circle cx="12" cy="12" r="3" />
        </template>
        <template v-else-if="name === 'eye-off'">
            <path d="M3.5 3.5l17 17" />
            <path d="M10 5.7A9.8 9.8 0 0 1 12 5.5c6 0 9.5 6.5 9.5 6.5a17 17 0 0 1-3.2 4M6.4 6.4C4 8.3 2.5 12 2.5 12s3.5 6.5 9.5 6.5c1.6 0 3-.4 4.2-1" />
            <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" />
        </template>
        <template v-else-if="name === 'logout'">
            <path d="M14 4h5v16h-5M10 8l-4 4 4 4M6 12h11" />
        </template>
        <template v-else-if="name === 'alert'">
            <path d="M12 3.5 2.5 20h19L12 3.5Z" />
            <path d="M12 10v4.5M12 17.5v.5" />
        </template>
        <template v-else-if="name === 'info'">
            <circle cx="12" cy="12" r="8.5" />
            <path d="M12 11v5.5M12 7.5v.5" />
        </template>
        <template v-else-if="name === 'ticket'">
            <path d="M3 8.5V6.5h18v2a2.5 2.5 0 0 0 0 7v2H3v-2a2.5 2.5 0 0 0 0-7Z" />
            <path d="M13.5 6.5v1.5M13.5 11v2M13.5 16v1.5" stroke-dasharray="2 2" />
        </template>
    </svg>
</template>
