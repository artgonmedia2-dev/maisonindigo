<script setup lang="ts">
import MiBreadcrumb from '@/Components/mi/MiBreadcrumb.vue';
import { useI18n } from '@/composables/useI18n';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { SeoProps } from '@/types/Hub';
import type { JournalAuthorProps } from '@/types/Journal';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<JournalAuthorProps & { seo: SeoProps }>();

const { t } = useI18n();
</script>

<template>
    <Head>
        <title>{{ props.seo.title }}</title>
    </Head>

    <StorefrontLayout>
        <MiBreadcrumb :items="props.breadcrumb" />

        <section class="mi-container flex flex-wrap items-start gap-8 pt-6 md:pt-10">
            <img
                v-if="props.author.portrait"
                :src="props.author.portrait"
                :alt="props.author.name"
                class="aspect-square w-28 object-cover"
                loading="lazy"
            />
            <div class="min-w-[15rem] flex-1">
                <p class="mi-caps text-mi-stone">{{ t('journal.author') }}</p>
                <h1 class="mt-3 text-h1">{{ props.author.name }}</h1>
                <p v-if="props.author.role" class="mt-1 text-small text-mi-fil">{{ props.author.role }}</p>
                <p v-if="props.author.bio" class="mt-4 max-w-2xl text-[16px] leading-relaxed text-mi-charbon/85">
                    {{ props.author.bio }}
                </p>
            </div>
        </section>

        <section class="mi-container py-10 md:py-14">
            <h2 class="mi-caps text-mi-stone">{{ t('journal.allArticles') }}</h2>
            <ul v-if="props.articles.length > 0" class="mt-6 flex flex-col gap-8">
                <li v-for="article in props.articles" :key="article.slug">
                    <Link :href="`/journal/${article.slug}`" class="mi-link font-display text-h3 font-semibold text-mi-indigo">
                        {{ article.title }}
                    </Link>
                    <p class="mt-2 max-w-2xl text-[16px] leading-relaxed text-mi-charbon/85">{{ article.excerpt }}</p>
                </li>
            </ul>
            <p v-else class="mt-6 text-small text-mi-fil">{{ t('journal.empty') }}</p>
        </section>
    </StorefrontLayout>
</template>
