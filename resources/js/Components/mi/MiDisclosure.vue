<script setup lang="ts">
import MiIcon from '@/Components/mi/MiIcon.vue';
import { onMounted, ref } from 'vue';

/**
 * Section dépliable : fermée sur téléphone, ouverte sur grand écran.
 *
 * Le contenu reste dans le HTML dans les deux cas — un <details> fermé est lu
 * par les robots et par les moteurs génératifs. C'est l'affichage qui change,
 * pas la page.
 */
withDefaults(
    defineProps<{
        title: string;
        /** Niveau de titre, pour garder une hiérarchie correcte. */
        as?: 'h2' | 'h3';
    }>(),
    { as: 'h2' },
);

const open = ref(true);

onMounted(() => {
    open.value = typeof window === 'undefined' || window.matchMedia('(min-width: 768px)').matches;
});
</script>

<template>
    <details :open="open" class="group border-t border-mi-ligne py-5 md:py-6">
        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 md:cursor-default">
            <component :is="as" class="font-display text-h3 font-semibold leading-tight text-mi-indigo md:text-[1.75rem]">
                {{ title }}
            </component>
            <MiIcon
                name="chevron"
                :size="18"
                aria-hidden="true"
                class="shrink-0 text-mi-stone transition-transform duration-150 group-open:rotate-180 md:hidden"
            />
        </summary>
        <div class="mt-3 max-w-3xl text-[16px] leading-relaxed text-mi-charbon/85 md:mt-4">
            <slot />
        </div>
    </details>
</template>

<style scoped>
summary::-webkit-details-marker {
    display: none;
}
</style>
