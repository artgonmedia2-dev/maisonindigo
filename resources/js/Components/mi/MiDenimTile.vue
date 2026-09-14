<script setup lang="ts">
import MiLogo from '@/Components/mi/MiLogo.vue';

/**
 * Gabarit d'attente pour les visuels : toile indigo, surpiqûre, silhouette.
 * Remplacé par les photos produit sur fond écru dès qu'elles existent.
 * - collection : toile indigo (ou indigo nuit) avec une jambe de jean stylisée.
 * - story : écru en haut, denim en bas, une seule surpiqûre verticale (format story de la charte).
 */
type Variant = 'collection' | 'collection-deep' | 'story';

withDefaults(
    defineProps<{
        variant?: Variant;
        label: string;
    }>(),
    { variant: 'collection' },
);
</script>

<template>
    <div class="relative aspect-[4/5] w-full overflow-hidden" role="img" :aria-label="label">
        <template v-if="variant === 'story'">
            <div class="absolute inset-0 bg-mi-ecru" aria-hidden="true"></div>
            <div class="mi-twill absolute inset-x-0 bottom-0 h-[58%] bg-mi-indigo" aria-hidden="true">
                <div class="absolute inset-y-0 start-[14%] border-s-2 border-dashed border-mi-ocre"></div>
            </div>
            <div class="absolute start-5 top-5 md:start-7 md:top-7" aria-hidden="true">
                <MiLogo tone="indigo" :width="110" />
            </div>
            <div class="absolute inset-x-5 bottom-5 md:inset-x-7 md:bottom-7">
                <slot />
            </div>
        </template>

        <template v-else>
            <div
                class="mi-twill absolute inset-0 transition-opacity duration-150 group-hover:opacity-90"
                :class="variant === 'collection-deep' ? 'bg-mi-indigo-deep' : 'bg-mi-indigo'"
                aria-hidden="true"
            >
                <div class="absolute inset-x-[34%] inset-y-[9%] bg-mi-stone/55 [clip-path:polygon(0_0,100%_0,88%_100%,12%_100%)]"></div>
                <div class="absolute inset-y-[9%] start-[46%] border-s border-dashed border-mi-ocre/70"></div>
            </div>
            <div class="absolute start-4 top-4">
                <slot />
            </div>
        </template>
    </div>
</template>
