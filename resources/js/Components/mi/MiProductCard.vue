<script setup lang="ts">
import MiDenimTile from '@/Components/mi/MiDenimTile.vue';
import MiPatch from '@/Components/mi/MiPatch.vue';
import MiPrice from '@/Components/mi/MiPrice.vue';
import { useI18n } from '@/composables/useI18n';
import type { ProductCard } from '@/types/Catalog';
import { Link } from '@inertiajs/vue3';

/**
 * Carte produit : photo 4:5 sur fond écru, badge patch, « Coupe Lavage »,
 * sous-titre genre · matière · tailles, prix en chiffres tabulaires.
 */
withDefaults(
    defineProps<{
        product: ProductCard;
        /** Première ligne de la grille : chargement prioritaire. */
        eager?: boolean;
    }>(),
    { eager: false },
);

const { t } = useI18n();
</script>

<template>
    <article class="group">
        <Link :href="product.url" class="block">
            <div class="relative overflow-hidden bg-mi-ecru">
                <picture v-if="product.image">
                    <source v-if="product.image.avif_srcset" type="image/avif" :srcset="product.image.avif_srcset" sizes="(min-width: 768px) 33vw, 50vw" />
                    <img
                        :src="product.image.src"
                        :srcset="product.image.srcset || undefined"
                        sizes="(min-width: 768px) 33vw, 50vw"
                        :alt="product.image.alt"
                        :loading="eager ? 'eager' : 'lazy'"
                        :fetchpriority="eager ? 'high' : 'auto'"
                        decoding="async"
                        class="aspect-[4/5] w-full object-cover transition-opacity duration-150 group-hover:opacity-95"
                    />
                </picture>
                <MiDenimTile v-else :variant="product.gender === 'femme' ? 'collection' : 'collection-deep'" :label="product.title" />

                <span v-if="product.patch" class="absolute start-3 top-3">
                    <MiPatch :kind="product.patch" />
                </span>
                <span
                    v-if="!product.in_stock"
                    class="mi-caps absolute bottom-3 start-3 bg-mi-ecru/95 px-2.5 py-1.5 text-[11px] text-mi-charbon"
                >
                    {{ t('collection.soldOut') }}
                </span>
            </div>

            <!-- flex-wrap plus une largeur plancher : sur deux colonnes mobiles, le prix
                 passe sous le titre au lieu de l'écraser lettre par lettre. -->
            <div class="mt-4 flex flex-wrap items-start justify-between gap-x-4 gap-y-1">
                <div class="min-w-[7.5rem] flex-1">
                    <h3 class="font-display text-[1.25rem] font-semibold leading-tight text-mi-indigo">
                        <span class="mi-link">{{ product.title }}</span>
                    </h3>
                    <p class="mt-1 text-small text-mi-fil">{{ product.subtitle }}</p>
                </div>
                <MiPrice :amount="product.price" :compare-at="product.compare_at_price" class="shrink-0 pt-0.5" />
            </div>
        </Link>
    </article>
</template>
