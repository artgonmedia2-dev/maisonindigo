<script setup lang="ts">
import MiDenimTile from '@/Components/mi/MiDenimTile.vue';
import { useI18n } from '@/composables/useI18n';
import type { ProductImage } from '@/types/Catalog';
import { ref } from 'vue';

/**
 * Galerie produit : six vues ordonnées, ratio 4:5, fond écru.
 * Sans photographie, un gabarit d'attente avec une mention honnête.
 */
const props = defineProps<{
    images: ProductImage[];
    title: string;
    gender: 'homme' | 'femme';
}>();

const { t } = useI18n();

const current = ref(0);

const select = (index: number): void => {
    if (index >= 0 && index < props.images.length) {
        current.value = index;
    }
};
</script>

<template>
    <div v-if="images.length === 0" class="relative">
        <MiDenimTile :variant="gender === 'femme' ? 'collection' : 'collection-deep'" :label="title" />
        <p class="mt-3 text-small text-mi-fil">{{ t('product.placeholder') }}</p>
    </div>

    <div v-else class="flex flex-col gap-3 md:flex-row-reverse md:gap-4">
        <figure class="relative min-w-0 flex-1 bg-mi-ecru">
            <picture>
                <source type="image/avif" :srcset="images[current]?.avif_srcset" sizes="(min-width: 768px) 55vw, 100vw" />
                <img
                    :src="images[current]?.src"
                    :srcset="images[current]?.srcset"
                    sizes="(min-width: 768px) 55vw, 100vw"
                    :alt="images[current]?.alt"
                    class="aspect-[4/5] w-full object-cover"
                    :loading="current === 0 ? 'eager' : 'lazy'"
                    :fetchpriority="current === 0 ? 'high' : 'auto'"
                    decoding="async"
                />
            </picture>
            <figcaption class="sr-only">{{ t('product.viewOf', { current: current + 1, total: images.length }) }}</figcaption>
        </figure>

        <ul v-if="images.length > 1" class="flex gap-2 overflow-x-auto md:w-20 md:flex-col md:overflow-visible" :aria-label="t('product.views')">
            <li v-for="(image, index) in images" :key="image.src" class="shrink-0">
                <button
                    type="button"
                    class="block w-16 border-2 bg-mi-ecru transition-colors duration-150 md:w-20"
                    :class="index === current ? 'border-mi-indigo' : 'border-transparent hover:border-mi-ligne'"
                    :aria-label="t('product.viewOf', { current: index + 1, total: images.length })"
                    :aria-pressed="index === current"
                    @click="select(index)"
                >
                    <img :src="image.src" :alt="''" class="aspect-[4/5] w-full object-cover" loading="lazy" decoding="async" />
                </button>
            </li>
        </ul>
    </div>
</template>
