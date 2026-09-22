<script setup lang="ts">
import MiDenimTile from '@/Components/mi/MiDenimTile.vue';
import MiIcon from '@/Components/mi/MiIcon.vue';
import MiPatch from '@/Components/mi/MiPatch.vue';
import { useI18n } from '@/composables/useI18n';
import { useRoute } from '@/composables/useRoute';
import type { HomeCollectionCard, HomeCollections } from '@/types/Home';
import { Link } from '@inertiajs/vue3';

/**
 * Les deux portes d'entrée du catalogue : femme, homme.
 *
 * Placée juste après le hero, elle répond à la première question que se pose
 * un visiteur — « où est mon rayon » — avant de lui montrer des modèles qui
 * ne le concernent peut-être pas. Les deux choix tiennent côte à côte, y
 * compris sur téléphone : aucun défilement pour décider.
 */
withDefaults(
    defineProps<{
        collections: HomeCollections;
        /** Filet de séparation quand la section suit un autre bloc. */
        divider?: boolean;
    }>(),
    { divider: false },
);

const { t } = useI18n();
const route = useRoute();

/** Le nombre de modèles rassure ; une collection vide ne l'annonce pas. */
const countLabel = (card: HomeCollectionCard): string => {
    if (card.count === 0) {
        return t('home.collectionSoon');
    }

    return card.count === 1 ? t('home.collectionCountOne') : t('home.collectionCount', { count: card.count });
};
</script>

<template>
    <section class="mi-container py-14 md:py-20" :class="divider ? 'border-t border-mi-ligne' : ''">
        <div class="max-w-2xl">
            <p class="mi-caps text-mi-stone">{{ collections.kicker }}</p>
            <h2 class="mt-4 text-h2 md:text-[2.5rem] md:leading-[1.1]">{{ collections.title }}</h2>
        </div>

        <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-8 md:mt-10 md:gap-8">
            <article v-for="side in ['women', 'men'] as const" :key="side" class="group">
                <Link :href="route(side === 'women' ? 'collections.women' : 'collections.men')" class="block">
                    <div class="relative">
                        <MiDenimTile :variant="side === 'women' ? 'collection' : 'collection-deep'" :label="collections[side].title">
                            <MiPatch :kind="side === 'women' ? 'new' : 'atelier'" />
                        </MiDenimTile>
                    </div>

                    <div class="mt-4 flex flex-wrap items-start justify-between gap-x-6 gap-y-2 md:mt-5">
                        <div class="min-w-[7.5rem] flex-1">
                            <h3 class="text-h3 md:text-h2">{{ collections[side].title }}</h3>
                            <p class="mt-1.5 text-[15px] text-mi-fil md:mt-2">{{ collections[side].text }}</p>
                            <p class="mi-caps mt-2 text-[11px] text-mi-stone">{{ countLabel(collections[side]) }}</p>
                        </div>
                        <span class="mt-1 inline-flex shrink-0 items-center gap-2 text-[15px] font-semibold text-mi-stone md:mt-2">
                            {{ t('home.discover') }}
                            <MiIcon name="arrow" :size="18" class="transition-transform duration-150 group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5" />
                        </span>
                    </div>
                </Link>
            </article>
        </div>
    </section>
</template>
