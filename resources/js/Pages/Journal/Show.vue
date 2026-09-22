<script setup lang="ts">
import MiBreadcrumb from '@/Components/mi/MiBreadcrumb.vue';
import MiDisclosure from '@/Components/mi/MiDisclosure.vue';
import MiProductCard from '@/Components/mi/MiProductCard.vue';
import { useI18n } from '@/composables/useI18n';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { SeoProps } from '@/types/Hub';
import type { JournalShowProps } from '@/types/Journal';
import { Head, Link } from '@inertiajs/vue3';

/**
 * Gabarit d'article : titre, « En bref », sections H2, encadré produits, FAQ,
 * auteur et dates. La réponse directe passe avant tout récit — c'est ce
 * fragment que les moteurs génératifs reprennent.
 */
const props = defineProps<JournalShowProps & { seo: SeoProps }>();

const { t } = useI18n();
</script>

<template>
    <Head>
        <title>{{ props.seo.title }}</title>
    </Head>

    <StorefrontLayout>
        <MiBreadcrumb :items="props.breadcrumb" />

        <article class="mi-container py-6 md:py-10">
            <header class="max-w-3xl">
                <h1 class="text-hero">{{ props.article.title }}</h1>

                <div class="mt-6 border-s-2 border-mi-ocre ps-5">
                    <p class="mi-caps text-[11px] text-mi-stone">{{ t('journal.brief') }}</p>
                    <p class="mt-2 text-[17px] leading-relaxed text-mi-charbon">{{ props.article.excerpt }}</p>
                </div>

                <p class="mt-5 text-small text-mi-fil">
                    <Link v-if="props.article.author" :href="props.article.author.url" class="mi-link">
                        {{ t('journal.by', { name: props.article.author.name }) }}
                    </Link>
                    <span v-if="props.article.published_at">
                        · {{ t('journal.published', { date: props.article.published_at }) }}
                    </span>
                    <span v-if="props.article.updated_at && props.article.updated_at !== props.article.published_at">
                        · {{ t('journal.updated', { date: props.article.updated_at }) }}
                    </span>
                </p>
            </header>

            <img
                v-if="props.article.cover"
                :src="props.article.cover"
                :alt="props.article.title"
                class="mt-10 aspect-[16/9] w-full object-cover"
                fetchpriority="high"
                decoding="async"
            />

            <div v-if="props.article.blocks.length > 0" class="mt-10 max-w-3xl">
                <section v-for="block in props.article.blocks" :key="block.title" class="mt-8 first:mt-0">
                    <h2 class="font-display text-h2 font-semibold leading-tight text-mi-indigo">{{ block.title }}</h2>
                    <p class="mt-3 whitespace-pre-line text-[16px] leading-relaxed text-mi-charbon/85">{{ block.body }}</p>
                </section>
            </div>
        </article>

        <section v-if="props.products.length > 0" class="mi-container border-t border-mi-ligne py-10 md:py-14">
            <h2 class="font-display text-h2 font-semibold leading-tight text-mi-indigo">{{ t('journal.products') }}</h2>
            <div class="mt-8 grid grid-cols-2 gap-x-5 gap-y-10 md:grid-cols-3 md:gap-x-8">
                <MiProductCard v-for="product in props.products" :key="product.id" :product="product" />
            </div>
        </section>

        <section v-if="props.article.faq.length > 0" class="mi-container border-t border-mi-ligne py-10 md:py-14">
            <h2 class="font-display text-h2 font-semibold leading-tight text-mi-indigo">{{ t('journal.faq') }}</h2>
            <div class="mt-6">
                <MiDisclosure v-for="entry in props.article.faq" :key="entry.question" :title="entry.question" as="h3">
                    <p class="whitespace-pre-line">{{ entry.answer }}</p>
                </MiDisclosure>
            </div>
        </section>
    </StorefrontLayout>
</template>
