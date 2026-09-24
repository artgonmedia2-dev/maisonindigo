<script setup lang="ts">
import MiBrandStrip from '@/Components/mi/MiBrandStrip.vue';
import MiButton from '@/Components/mi/MiButton.vue';
import MiCollectionSplit from '@/Components/mi/MiCollectionSplit.vue';
import MiIcon, { type MiIconName } from '@/Components/mi/MiIcon.vue';
import MiPatch from '@/Components/mi/MiPatch.vue';
import MiProductCard from '@/Components/mi/MiProductCard.vue';
import MiReviewRail from '@/Components/mi/MiReviewRail.vue';
import { useI18n } from '@/composables/useI18n';
import { useMoney } from '@/composables/useMoney';
import { useRoute } from '@/composables/useRoute';
import type { TranslationKey } from '@/i18n';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { HomePageProps } from '@/types/Home';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<HomePageProps>();

const { t } = useI18n();
const { format } = useMoney();
const route = useRoute();

const trust: ReadonlyArray<{ icon: MiIconName; key: TranslationKey }> = [
    { icon: 'cash', key: 'home.trustCod' },
    { icon: 'exchange', key: 'home.trustExchange' },
    { icon: 'box', key: 'home.trustShipping' },
];

const arguments_: ReadonlyArray<{ index: string; title: TranslationKey; text: TranslationKey }> = [
    { index: '01', title: 'home.argDenimTitle', text: 'home.argDenimText' },
    { index: '02', title: 'home.argSizesTitle', text: 'home.argSizesText' },
    { index: '03', title: 'home.argServiceTitle', text: 'home.argServiceText' },
];

const heroWebp = '/images/hero-480.webp 480w, /images/hero-800.webp 800w, /images/hero-1200.webp 1200w';
const heroAvif = '/images/hero-480.avif 480w, /images/hero-800.avif 800w, /images/hero-1200.avif 1200w';
const heroSizes = '(min-width: 1024px) 34vw, (min-width: 768px) 42vw, 100vw';
</script>

<template>
    <Head>
        <title>{{ props.meta.title ?? '' }}</title>
        <meta v-if="props.meta.description" name="description" :content="props.meta.description" />
    </Head>

    <StorefrontLayout>
        <!-- Ouverture : ce que c'est, combien ça coûte, à quoi ça ressemble,
             et ce qui lève les trois objections du client marocain. -->
        <section class="mi-container grid grid-cols-1 items-center gap-10 py-12 md:grid-cols-12 md:py-20 lg:gap-16">
            <div class="md:col-span-7">
                <p class="mi-caps text-mi-stone">{{ props.hero.kicker }}</p>
                <h1 class="mt-5 max-w-3xl text-hero">{{ props.hero.title }}</h1>
                <p class="mt-6 max-w-xl text-[17px] leading-relaxed text-mi-charbon/85">{{ props.hero.lead }}</p>

                <p v-if="props.hero.from_price !== null" class="mt-6 font-display text-[1.375rem] font-semibold text-mi-indigo">
                    {{ t('home.fromPrice', { price: format(props.hero.from_price) }) }}
                </p>

                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <MiButton variant="primary" :href="props.hero.cta.url" arrow>{{ props.hero.cta.label }}</MiButton>
                    <MiButton variant="outline" :href="route('size-quiz')">{{ t('home.ctaQuiz') }}</MiButton>
                </div>

                <!-- Les trois réponses qui débloquent un achat au Maroc :
                     comment on paie, ce qu'il advient d'une taille qui ne va
                     pas, et quand le colis arrive. -->
                <ul class="mt-10 flex flex-col gap-3 text-small font-medium text-mi-charbon sm:flex-row sm:flex-wrap sm:gap-x-7">
                    <li v-for="item in trust" :key="item.key" class="flex items-center gap-2.5">
                        <MiIcon :name="item.icon" :size="17" class="shrink-0 text-mi-vert" />
                        {{ t(item.key) }}
                    </li>
                </ul>
            </div>

            <div class="md:col-span-5">
                <figure class="relative overflow-hidden bg-mi-ecru">
                    <picture>
                        <source type="image/avif" :srcset="heroAvif" :sizes="heroSizes" />
                        <img
                            src="/images/hero-800.webp"
                            :srcset="heroWebp"
                            :sizes="heroSizes"
                            width="800"
                            height="1000"
                            :alt="t('home.heroAlt')"
                            fetchpriority="high"
                            decoding="async"
                            class="aspect-[4/5] w-full object-cover"
                        />
                    </picture>

                    <span class="absolute start-4 top-4"><MiPatch kind="new" /></span>

                    <!-- Un chemin direct vers un modèle, prix compris : le
                         visiteur n'a pas à traverser une collection pour voir
                         un jean et son tarif. -->
                    <figcaption
                        v-if="props.hero.product"
                        class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-mi-indigo-deep/90 via-mi-indigo-deep/70 to-transparent p-5 pt-12 text-mi-ecru"
                    >
                        <p class="mi-caps text-[11px] text-mi-ciel">{{ t('home.heroProductKicker') }}</p>
                        <div class="mt-2 flex flex-wrap items-end justify-between gap-x-5 gap-y-2">
                            <div class="min-w-[9rem] flex-1">
                                <p class="font-display text-[1.375rem] font-semibold leading-tight">{{ props.hero.product.title }}</p>
                                <p class="mt-0.5 text-small text-mi-ciel">{{ props.hero.product.subtitle }}</p>
                            </div>
                            <p class="font-display text-[1.25rem] font-semibold tabular-nums">{{ format(props.hero.product.price) }}</p>
                        </div>
                        <Link
                            :href="props.hero.product.url"
                            class="mi-link mt-3 inline-flex items-center gap-1.5 text-small font-semibold"
                        >
                            {{ t('home.heroProductCta') }} <MiIcon name="arrow" :size="16" />
                        </Link>
                    </figcaption>
                </figure>
            </div>
        </section>

        <MiBrandStrip :title="props.brands.title" :brands="props.brands.items" />

        <!-- La surpiqûre ne sert que si le bandeau de logos est retiré :
             sans lui, l'ouverture toucherait la section suivante. -->
        <div v-if="props.brands.items.length === 0" class="mi-container">
            <hr class="mi-stitch border-0" />
        </div>

        <!-- Le premier choix du visiteur : femme ou homme, avant les modèles. -->
        <MiCollectionSplit v-if="props.collections.first" :collections="props.collections" />

        <!-- Nouveautés -->
        <section
            v-if="props.newProducts.length > 0"
            class="mi-container py-16 md:py-24"
            :class="props.collections.first ? 'border-t border-mi-ligne' : ''"
        >
            <div class="flex flex-wrap items-end justify-between gap-6">
                <div class="max-w-2xl">
                    <p class="mi-caps text-mi-stone">{{ t('home.newKicker') }}</p>
                    <h2 class="mt-4 text-h2 md:text-[2.5rem] md:leading-[1.1]">{{ t('home.newTitle') }}</h2>
                </div>
                <MiButton variant="ghost" :href="route('collections.new')" arrow>{{ t('home.newAll') }}</MiButton>
            </div>
            <div class="mt-10 grid grid-cols-2 gap-x-5 gap-y-10 md:grid-cols-3 md:gap-x-8">
                <MiProductCard
                    v-for="(product, index) in props.newProducts"
                    :key="product.id"
                    :product="product"
                    :eager="index < 2"
                    :class="index === 3 ? 'md:hidden' : ''"
                />
            </div>
        </section>

        <MiCollectionSplit
            v-if="!props.collections.first"
            :collections="props.collections"
            :divider="props.newProducts.length > 0"
        />

        <!-- Les arguments de la maison -->
        <section class="border-y border-mi-ligne bg-mi-blanc">
            <div class="mi-container py-16 md:py-24">
                <div class="max-w-2xl">
                    <p class="mi-caps text-mi-stone">{{ t('home.argKicker') }}</p>
                    <h2 class="mt-4 text-h2 md:text-[2.5rem] md:leading-[1.1]">{{ t('home.argTitle') }}</h2>
                </div>

                <ol class="mt-12 grid grid-cols-1 gap-10 md:grid-cols-3 md:gap-0">
                    <li
                        v-for="(item, index) in arguments_"
                        :key="item.index"
                        class="md:px-8 first:md:ps-0 last:md:pe-0"
                        :class="index > 0 ? 'md:border-s md:border-mi-ligne' : ''"
                    >
                        <p class="font-display text-h2 text-mi-stone/60" aria-hidden="true">{{ item.index }}</p>
                        <h3 class="mt-3 text-h3">{{ t(item.title) }}</h3>
                        <p class="mt-3 text-[15px] leading-relaxed text-mi-charbon/85">{{ t(item.text) }}</p>
                    </li>
                </ol>
            </div>
        </section>

        <MiReviewRail :reviews="props.reviews" />

        <!-- Le conseil de l'atelier -->
        <section class="mi-twill bg-mi-indigo-deep text-mi-ecru">
            <div class="mi-container py-20 md:py-28">
                <figure class="mx-auto max-w-3xl text-center">
                    <blockquote class="font-display text-[1.875rem] font-medium italic leading-[1.2] md:text-[2.75rem]">
                        {{ t('home.quote') }}
                    </blockquote>
                    <figcaption class="mi-caps mt-8 text-mi-ciel">{{ t('home.quoteCite') }}</figcaption>
                </figure>
                <div class="mt-10 flex justify-center">
                    <MiButton variant="outline" tone="ecru" :href="route('size-quiz')">{{ t('home.ctaQuiz') }}</MiButton>
                </div>
            </div>
        </section>

        <section v-if="props.newProducts.length === 0" class="mi-container py-12 text-center text-small text-mi-fil">
            {{ t('home.comingSoon') }}
        </section>
    </StorefrontLayout>
</template>
