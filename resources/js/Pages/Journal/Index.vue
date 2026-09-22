<script setup lang="ts">
import MiBreadcrumb from '@/Components/mi/MiBreadcrumb.vue';
import { useI18n } from '@/composables/useI18n';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { SeoProps } from '@/types/Hub';
import type { JournalIndexProps } from '@/types/Journal';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<JournalIndexProps & { seo: SeoProps }>();

const { t } = useI18n();
</script>

<template>
    <Head>
        <title>{{ props.seo.title }}</title>
    </Head>

    <StorefrontLayout>
        <MiBreadcrumb :items="props.breadcrumb" />

        <section class="mi-container pt-6 md:pt-8">
            <h1 class="text-hero">{{ t('journal.title') }}</h1>
            <p class="mt-6 max-w-2xl text-[17px] leading-relaxed text-mi-charbon/85">{{ t('journal.lead') }}</p>
        </section>

        <section class="mi-container py-10 md:py-14">
            <ul v-if="props.articles.length > 0" class="grid grid-cols-1 gap-10 md:grid-cols-2 md:gap-12">
                <li v-for="article in props.articles" :key="article.slug">
                    <article>
                        <Link :href="`/journal/${article.slug}`" class="block">
                            <div class="overflow-hidden bg-mi-ecru">
                                <img
                                    v-if="article.cover"
                                    :src="article.cover"
                                    :alt="article.title"
                                    class="aspect-[16/9] w-full object-cover"
                                    loading="lazy"
                                    decoding="async"
                                />
                                <div v-else class="mi-twill aspect-[16/9] w-full bg-mi-indigo" aria-hidden="true"></div>
                            </div>
                            <h2 class="mt-5 font-display text-h2 font-semibold leading-tight text-mi-indigo">
                                <span class="mi-link">{{ article.title }}</span>
                            </h2>
                        </Link>
                        <p class="mt-3 text-[16px] leading-relaxed text-mi-charbon/85">{{ article.excerpt }}</p>
                        <p v-if="article.author" class="mi-caps mt-3 text-[11px] text-mi-stone">
                            {{ t('journal.by', { name: article.author }) }}
                        </p>
                    </article>
                </li>
            </ul>
            <p v-else class="text-small text-mi-fil">{{ t('journal.empty') }}</p>
        </section>
    </StorefrontLayout>
</template>
