<script setup lang="ts">
import MiIcon from '@/Components/mi/MiIcon.vue';
import { useI18n } from '@/composables/useI18n';
import type { HomeReviews } from '@/types/Home';
import { Link } from '@inertiajs/vue3';

/**
 * Les avis clients.
 *
 * Sur téléphone, un rail qui se fait glisser du doigt, avec accrochage par
 * carte : c'est le geste attendu, et il tient dans la hauteur d'écran au lieu
 * d'empiler six cartes que personne ne fait défiler. Au-delà de 768 px, une
 * grille de trois, sans défilement.
 *
 * Aucune animation automatique : la charte ne les admet pas, et un avis qui
 * s'échappe pendant qu'on le lit est une mauvaise idée de toute façon.
 */
defineProps<{
    reviews: HomeReviews;
}>();

const { t } = useI18n();
</script>

<template>
    <section v-if="reviews.items.length > 0" class="border-t border-mi-ligne py-12 md:py-16">
        <div class="mi-container flex flex-wrap items-end justify-between gap-x-8 gap-y-4">
            <h2 class="font-display text-h2 font-semibold leading-tight text-mi-indigo md:text-[2.5rem] md:leading-[1.1]">
                {{ reviews.title }}
            </h2>

            <!-- La moyenne porte sur tous les avis publiés, pas sur les six montrés. -->
            <p v-if="reviews.average !== null" class="flex items-baseline gap-2.5 text-small text-mi-fil">
                <span class="font-display text-[1.5rem] font-semibold tabular-nums text-mi-indigo">
                    {{ reviews.average.toFixed(1).replace('.', ',') }}
                </span>
                {{ t('reviews.outOf', { count: reviews.count }) }}
            </p>
        </div>

        <!-- Rail glissable sur téléphone, grille au-delà. Le débordement est
             porté par un conteneur pleine largeur pour que la première carte
             s'aligne sur la marge de la page. -->
        <div class="mi-reviews mt-8 md:mt-10">
            <ul class="mi-reviews-rail mi-container">
                <li v-for="(review, index) in reviews.items" :key="index" class="mi-reviews-card">
                    <figure class="flex h-full flex-col border border-mi-ligne bg-mi-blanc p-6">
                        <div class="flex items-center gap-1" :aria-label="t('reviews.rating', { rating: review.rating })">
                            <MiIcon
                                v-for="star in 5"
                                :key="star"
                                name="check"
                                :size="14"
                                aria-hidden="true"
                                :class="star <= review.rating ? 'text-mi-ocre' : 'text-mi-ligne'"
                            />
                        </div>

                        <blockquote class="mt-4 flex-1 text-[16px] leading-relaxed text-mi-charbon/85">
                            {{ review.body }}
                        </blockquote>

                        <figcaption class="mt-5 border-t border-mi-ligne pt-4">
                            <p class="text-[15px] font-semibold text-mi-indigo">
                                {{ review.author }}<span v-if="review.city" class="font-normal text-mi-fil"> · {{ review.city }}</span>
                            </p>

                            <p class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-small text-mi-fil">
                                <span v-if="review.size">{{ t('reviews.size', { size: review.size }) }}</span>

                                <!-- Le badge ne s'affiche que si une commande livrée
                                     porte l'avis : il ne se saisit pas à la main. -->
                                <span v-if="review.verified" class="mi-caps inline-flex items-center gap-1 text-[10px] text-mi-vert">
                                    <MiIcon name="check" :size="12" aria-hidden="true" />
                                    {{ t('reviews.verified') }}
                                </span>
                            </p>

                            <Link
                                v-if="review.product"
                                :href="review.product.url"
                                class="mi-link mt-2 inline-block text-small text-mi-stone"
                            >
                                {{ review.product.title }}
                            </Link>
                        </figcaption>
                    </figure>
                </li>
            </ul>
        </div>
    </section>
</template>

<style scoped>
.mi-reviews-rail {
    display: grid;
    grid-auto-flow: column;
    grid-auto-columns: minmax(16.5rem, 78%);
    gap: 1rem;
    overflow-x: auto;
    /* Chaque carte s'accroche : on ne s'arrête jamais entre deux. */
    scroll-snap-type: x mandatory;
    overscroll-behavior-inline: contain;
    scrollbar-width: none;
    padding-block-end: 0.25rem;
}

.mi-reviews-rail::-webkit-scrollbar {
    display: none;
}

.mi-reviews-card {
    scroll-snap-align: start;
}

@media (width >= 48rem) {
    .mi-reviews-rail {
        grid-auto-flow: row;
        grid-auto-columns: auto;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 2rem;
        overflow-x: visible;
        scroll-snap-type: none;
    }

    /* Au-delà de trois avis, la grille s'allonge en lignes de trois. */
    .mi-reviews-card {
        scroll-snap-align: none;
    }
}
</style>
