<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import MiIcon from '@/Components/mi/MiIcon.vue';
import MiPatch from '@/Components/mi/MiPatch.vue';
import { useI18n } from '@/composables/useI18n';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { HomePageProps } from '@/types/Home';
import { Head } from '@inertiajs/vue3';

const props = defineProps<HomePageProps>();

const { t } = useI18n();
</script>

<template>
    <Head>
        <title>{{ props.meta.title ?? '' }}</title>
        <meta v-if="props.meta.description" name="description" :content="props.meta.description" />
    </Head>

    <StorefrontLayout>
        <!-- Hero éditorial -->
        <section class="mi-container py-16 md:py-28">
            <p class="mi-caps text-mi-stone">{{ t('brand.kicker') }}</p>
            <h1 class="mt-5 max-w-3xl font-display text-[2.75rem] leading-[1.05] font-semibold text-mi-indigo md:text-[4.5rem]">
                {{ t('home.title') }}
            </h1>
            <p class="mt-6 max-w-xl text-[17px] leading-relaxed text-mi-charbon/85">
                {{ t('home.lead') }}
            </p>
            <div class="mt-10 flex flex-wrap items-center gap-4">
                <MiButton variant="primary" :href="route('size-quiz')">{{ t('home.ctaQuiz') }}</MiButton>
                <MiButton variant="outline" :href="route('collections.new')">{{ t('home.ctaNew') }}</MiButton>
            </div>
        </section>

        <div class="mi-container">
            <hr class="mi-stitch border-0" />
        </div>

        <!-- Femme / Homme -->
        <section class="mi-container grid grid-cols-1 gap-8 py-16 md:grid-cols-2 md:py-24">
            <article class="group">
                <a :href="route('collections.women')" class="block">
                    <div class="mi-twill relative aspect-[4/5] bg-mi-indigo" role="img" :aria-label="t('home.womenTitle')">
                        <span class="absolute start-4 top-4"><MiPatch kind="new" /></span>
                        <div class="absolute inset-x-[33%] inset-y-[9%] bg-mi-stone/55 [clip-path:polygon(0_0,100%_0,88%_100%,12%_100%)]" aria-hidden="true"></div>
                    </div>
                    <h2 class="mt-5 text-h2">{{ t('home.womenTitle') }}</h2>
                    <p class="mt-2 text-mi-fil">{{ t('home.womenText') }}</p>
                    <span class="mt-3 inline-flex items-center gap-2 text-[15px] font-semibold text-mi-stone underline-offset-4 group-hover:underline">
                        {{ t('home.discover') }} <MiIcon name="arrow" :size="18" />
                    </span>
                </a>
            </article>

            <article class="group">
                <a :href="route('collections.men')" class="block">
                    <div class="mi-twill relative aspect-[4/5] bg-mi-indigo-deep" role="img" :aria-label="t('home.menTitle')">
                        <span class="absolute start-4 top-4"><MiPatch kind="atelier" /></span>
                        <div class="absolute inset-x-[33%] inset-y-[9%] bg-mi-stone/55 [clip-path:polygon(0_0,100%_0,88%_100%,12%_100%)]" aria-hidden="true"></div>
                    </div>
                    <h2 class="mt-5 text-h2">{{ t('home.menTitle') }}</h2>
                    <p class="mt-2 text-mi-fil">{{ t('home.menText') }}</p>
                    <span class="mt-3 inline-flex items-center gap-2 text-[15px] font-semibold text-mi-stone underline-offset-4 group-hover:underline">
                        {{ t('home.discover') }} <MiIcon name="arrow" :size="18" />
                    </span>
                </a>
            </article>
        </section>

        <!-- Les arguments de la maison -->
        <section class="bg-mi-blanc">
            <div class="mi-container grid grid-cols-1 gap-10 py-16 md:grid-cols-3 md:gap-12 md:py-24">
                <div>
                    <h2 class="text-h3">{{ t('home.argDenimTitle') }}</h2>
                    <p class="mt-3 text-[15px] leading-relaxed text-mi-charbon/85">{{ t('home.argDenimText') }}</p>
                </div>
                <div>
                    <h2 class="text-h3">{{ t('home.argSizesTitle') }}</h2>
                    <p class="mt-3 text-[15px] leading-relaxed text-mi-charbon/85">{{ t('home.argSizesText') }}</p>
                </div>
                <div>
                    <h2 class="text-h3">{{ t('home.argServiceTitle') }}</h2>
                    <p class="mt-3 text-[15px] leading-relaxed text-mi-charbon/85">{{ t('home.argServiceText') }}</p>
                </div>
            </div>
        </section>

        <section class="mi-container py-12 text-center text-small text-mi-fil">
            {{ t('home.comingSoon') }}
        </section>
    </StorefrontLayout>
</template>
