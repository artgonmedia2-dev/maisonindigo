<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import MiDenimTile from '@/Components/mi/MiDenimTile.vue';
import MiIcon from '@/Components/mi/MiIcon.vue';
import MiPatch from '@/Components/mi/MiPatch.vue';
import { useI18n } from '@/composables/useI18n';
import { useRoute } from '@/composables/useRoute';
import type { TranslationKey } from '@/i18n';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { HomePageProps } from '@/types/Home';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<HomePageProps>();

const { t } = useI18n();
const route = useRoute();

const specs: TranslationKey[] = ['home.specDenim', 'home.specSizes', 'home.specLengths'];

const arguments_: ReadonlyArray<{ index: string; title: TranslationKey; text: TranslationKey }> = [
    { index: '01', title: 'home.argDenimTitle', text: 'home.argDenimText' },
    { index: '02', title: 'home.argSizesTitle', text: 'home.argSizesText' },
    { index: '03', title: 'home.argServiceTitle', text: 'home.argServiceText' },
];
</script>

<template>
    <Head>
        <title>{{ props.meta.title ?? '' }}</title>
        <meta v-if="props.meta.description" name="description" :content="props.meta.description" />
    </Head>

    <StorefrontLayout>
        <!-- Hero éditorial -->
        <section class="mi-container grid grid-cols-1 items-center gap-12 py-14 md:grid-cols-12 md:py-24 lg:gap-16">
            <div class="md:col-span-7">
                <p class="mi-caps text-mi-stone">{{ t('brand.kicker') }}</p>
                <h1 class="mt-6 max-w-3xl text-hero">{{ t('home.title') }}</h1>
                <p class="mt-7 max-w-xl text-[17px] leading-relaxed text-mi-charbon/85">{{ t('home.lead') }}</p>

                <div class="mt-10 flex flex-wrap items-center gap-4">
                    <MiButton variant="primary" :href="route('size-quiz')" arrow>{{ t('home.ctaQuiz') }}</MiButton>
                    <MiButton variant="outline" :href="route('collections.new')">{{ t('home.ctaNew') }}</MiButton>
                </div>

                <ul class="mt-12 flex flex-wrap gap-x-8 gap-y-3 text-small font-medium text-mi-charbon">
                    <li v-for="(spec, index) in specs" :key="spec" class="flex items-center gap-3">
                        <span v-if="index > 0" class="hidden h-4 border-s-2 border-dashed border-mi-ocre sm:block" aria-hidden="true"></span>
                        <MiIcon name="check" :size="16" class="text-mi-vert" />
                        {{ t(spec) }}
                    </li>
                </ul>
            </div>

            <div class="md:col-span-5">
                <MiDenimTile variant="story" :label="t('home.storyTitle')">
                    <p class="font-display text-[1.75rem] font-semibold leading-tight text-mi-ecru">{{ t('home.storyTitle') }}</p>
                    <p class="mt-2 text-small text-mi-ciel">{{ t('home.storyText') }}</p>
                    <MiButton variant="outline" tone="ecru" size="sm" :href="route('size-quiz')" class="mt-5">
                        {{ t('home.ctaQuiz') }}
                    </MiButton>
                </MiDenimTile>
            </div>
        </section>

        <div class="mi-container">
            <hr class="mi-stitch border-0" />
        </div>

        <!-- Femme / Homme -->
        <section class="mi-container py-16 md:py-24">
            <div class="max-w-2xl">
                <p class="mi-caps text-mi-stone">{{ t('home.collectionsKicker') }}</p>
                <h2 class="mt-4 text-h2 md:text-[2.5rem] md:leading-[1.1]">{{ t('home.collectionsTitle') }}</h2>
            </div>

            <div class="mt-10 grid grid-cols-1 gap-8 md:grid-cols-2 md:gap-10">
                <article class="group">
                    <Link :href="route('collections.women')" class="block">
                        <MiDenimTile variant="collection" :label="t('home.womenTitle')">
                            <MiPatch kind="new" />
                        </MiDenimTile>
                        <div class="mt-5 flex items-start justify-between gap-6">
                            <div>
                                <h3 class="text-h2">{{ t('home.womenTitle') }}</h3>
                                <p class="mt-2 text-[15px] text-mi-fil">{{ t('home.womenText') }}</p>
                            </div>
                            <span class="mt-2 inline-flex shrink-0 items-center gap-2 text-[15px] font-semibold text-mi-stone">
                                {{ t('home.discover') }}
                                <MiIcon name="arrow" :size="18" class="transition-transform duration-150 group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5" />
                            </span>
                        </div>
                    </Link>
                </article>

                <article class="group">
                    <Link :href="route('collections.men')" class="block">
                        <MiDenimTile variant="collection-deep" :label="t('home.menTitle')">
                            <MiPatch kind="atelier" />
                        </MiDenimTile>
                        <div class="mt-5 flex items-start justify-between gap-6">
                            <div>
                                <h3 class="text-h2">{{ t('home.menTitle') }}</h3>
                                <p class="mt-2 text-[15px] text-mi-fil">{{ t('home.menText') }}</p>
                            </div>
                            <span class="mt-2 inline-flex shrink-0 items-center gap-2 text-[15px] font-semibold text-mi-stone">
                                {{ t('home.discover') }}
                                <MiIcon name="arrow" :size="18" class="transition-transform duration-150 group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5" />
                            </span>
                        </div>
                    </Link>
                </article>
            </div>
        </section>

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

        <section class="mi-container py-12 text-center text-small text-mi-fil">
            {{ t('home.comingSoon') }}
        </section>
    </StorefrontLayout>
</template>
