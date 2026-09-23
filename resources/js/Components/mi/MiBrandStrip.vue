<script setup lang="ts">
import { computed } from 'vue';

/**
 * Bandeau de logos qui défile.
 *
 * Deux copies de la liste se suivent et l'ensemble glisse d'exactement une
 * copie : la boucle se referme sans saut. Le défilement est en CSS pur, donc
 * il tourne sur le compositeur et ne coûte rien au fil principal, y compris
 * sur un téléphone d'entrée de gamme.
 *
 * Les bords sont estompés par un masque, pas par un dégradé posé au-dessus :
 * le fond de page reste visible quel qu'il soit.
 *
 * Il s'immobilise au survol et pour qui a demandé moins d'animations.
 */
const props = withDefaults(
    defineProps<{
        title: string;
        brands: ReadonlyArray<{ name: string; file: string }>;
        /** Durée d'un tour complet, en secondes. */
        duration?: number;
    }>(),
    { duration: 38 },
);

/** La liste doublée : la seconde copie est décorative, donc masquée aux lecteurs d'écran. */
const loop = computed(() => [...props.brands, ...props.brands]);
</script>

<template>
    <section v-if="brands.length > 0" class="border-y border-mi-ligne bg-mi-blanc py-8 md:py-10">
        <h2 class="mi-container mi-caps text-center text-[11px] text-mi-fil">{{ title }}</h2>

        <div class="mi-marquee mt-6 md:mt-7" :style="{ '--mi-marquee-duration': `${duration}s` }">
            <ul class="mi-marquee-track">
                <li
                    v-for="(brand, index) in loop"
                    :key="`${brand.file}-${index}`"
                    class="shrink-0"
                    :data-clone="index >= brands.length ? 'true' : undefined"
                    :aria-hidden="index >= brands.length ? 'true' : undefined"
                >
                    <img
                        :src="`/images/brands/${brand.file}`"
                        :alt="index >= brands.length ? '' : brand.name"
                        class="h-7 w-auto max-w-[8.5rem] object-contain opacity-55 grayscale md:h-9 md:max-w-[10.5rem]"
                        loading="lazy"
                        decoding="async"
                    />
                </li>
            </ul>
        </div>
    </section>
</template>

<style scoped>
.mi-marquee {
    /* Les bords s'effacent au lieu d'être coupés net. */
    mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent);
    -webkit-mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent);
    overflow: hidden;
}

.mi-marquee-track {
    --mi-marquee-gap: 2.5rem;

    display: flex;
    align-items: center;
    gap: var(--mi-marquee-gap);
    inline-size: max-content;
    animation: mi-marquee var(--mi-marquee-duration, 38s) linear infinite;
}

@media (width >= 48rem) {
    .mi-marquee-track {
        --mi-marquee-gap: 4rem;
    }
}

.mi-marquee:hover .mi-marquee-track,
.mi-marquee:focus-within .mi-marquee-track {
    animation-play-state: paused;
}

@keyframes mi-marquee {
    from {
        transform: translate3d(0, 0, 0);
    }
    to {
        /* Une copie entière, plus la moitié de l'écart qui la suit : c'est
           exactement le point où la seconde copie prend la place de la
           première, donc la boucle ne saute pas. */
        transform: translate3d(calc(-50% - var(--mi-marquee-gap) / 2), 0, 0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .mi-marquee-track {
        animation: none;
        flex-wrap: wrap;
        justify-content: center;
        inline-size: 100%;
        gap: 1.75rem 2.5rem;
    }

    /* Sans défilement, la seconde copie n'a plus de rôle : elle afficherait
       chaque logo deux fois. */
    .mi-marquee-track [data-clone] {
        display: none;
    }

    .mi-marquee {
        mask-image: none;
        -webkit-mask-image: none;
    }
}
</style>
