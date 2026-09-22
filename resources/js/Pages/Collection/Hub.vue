<script setup lang="ts">
import MiBreadcrumb from '@/Components/mi/MiBreadcrumb.vue';
import MiButton from '@/Components/mi/MiButton.vue';
import MiCheckbox from '@/Components/mi/MiCheckbox.vue';
import MiDisclosure from '@/Components/mi/MiDisclosure.vue';
import MiPagination from '@/Components/mi/MiPagination.vue';
import MiProductCard from '@/Components/mi/MiProductCard.vue';
import MiSelect from '@/Components/mi/MiSelect.vue';
import { useI18n } from '@/composables/useI18n';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { HubPageProps } from '@/types/Hub';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive, watch } from 'vue';

/**
 * Hub de coupe et sous-collection lavage.
 *
 * L'ordre des blocs suit la structure SEO : titre, réponse d'entrée, grille,
 * contenu enrichi, FAQ, maillage. Le contenu enrichi est replié sur téléphone
 * mais présent dans le HTML : c'est lui que les moteurs génératifs lisent.
 */
const props = defineProps<HubPageProps>();

const { t } = useI18n();

const state = reactive({
    wash: [...props.filters.wash],
    size: props.filters.size,
    length: props.filters.length,
    sort: props.filters.sort,
});

/** Le lavage de l'adresse n'est pas une facette : il ne se décoche pas. */
const washIsPath = computed(() => props.washPath !== null);

const resultsLabel = computed(() =>
    t(props.pagination.total === 1 ? 'collection.results' : 'collection.resultsPlural', { count: props.pagination.total }),
);

const sortOptions = computed(() => props.options.sorts.map((sort) => ({ value: sort.value, label: sort.label })));

const apply = (): void => {
    router.get(
        window.location.pathname,
        {
            ...(washIsPath.value ? {} : { wash: state.wash }),
            ...(state.size === null ? {} : { size: state.size }),
            ...(state.length === null ? {} : { length: state.length }),
            ...(state.sort === 'new' ? {} : { sort: state.sort }),
        },
        { preserveScroll: true, preserveState: true, replace: true },
    );
};

watch(() => [state.wash, state.size, state.length, state.sort], apply, { deep: true });

const toggleWash = (value: string, checked: boolean): void => {
    state.wash = checked ? [...state.wash, value] : state.wash.filter((item) => item !== value);
};
</script>

<template>
    <Head>
        <title>{{ props.seo.title }}</title>
    </Head>

    <StorefrontLayout>
        <MiBreadcrumb :items="props.breadcrumb" />

        <section class="mi-container pt-6 md:pt-8">
            <h1 class="text-hero">{{ props.heading }}</h1>
            <p v-if="props.intro" class="mt-6 max-w-2xl text-[17px] leading-relaxed text-mi-charbon/85">
                {{ props.intro }}
            </p>
        </section>

        <section class="mi-container grid grid-cols-1 gap-10 py-10 md:grid-cols-12 md:gap-10 md:py-14">
            <aside class="md:col-span-3">
                <h2 class="mi-caps text-mi-stone">{{ t('collection.filters') }}</h2>

                <div v-if="!washIsPath && props.options.washes.length > 0" class="mt-5">
                    <p class="text-small font-semibold text-mi-charbon">{{ t('collection.wash') }}</p>
                    <div class="mt-2 flex flex-col gap-1.5">
                        <MiCheckbox
                            v-for="wash in props.options.washes"
                            :key="wash.value"
                            :model-value="state.wash.includes(wash.value)"
                            :label="wash.label"
                            @update:model-value="toggleWash(wash.value, $event)"
                        />
                    </div>
                </div>

                <div v-if="props.options.sizes.length > 0" class="mt-6">
                    <p class="text-small font-semibold text-mi-charbon">{{ t('collection.size') }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button
                            v-for="size in props.options.sizes"
                            :key="size"
                            type="button"
                            class="flex h-10 w-12 items-center justify-center border-[1.5px] text-[14px] font-medium transition-colors duration-150"
                            :class="state.size === size ? 'border-mi-indigo bg-mi-indigo text-mi-ecru' : 'border-mi-ligne hover:border-mi-stone'"
                            :aria-pressed="state.size === size"
                            @click="state.size = state.size === size ? null : size"
                        >
                            {{ size }}
                        </button>
                    </div>
                </div>

                <div v-if="props.options.lengths.length > 0" class="mt-6">
                    <p class="text-small font-semibold text-mi-charbon">{{ t('collection.length') }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button
                            v-for="length in props.options.lengths"
                            :key="length"
                            type="button"
                            class="flex h-10 w-12 items-center justify-center border-[1.5px] text-[14px] font-medium transition-colors duration-150"
                            :class="state.length === length ? 'border-mi-indigo bg-mi-indigo text-mi-ecru' : 'border-mi-ligne hover:border-mi-stone'"
                            :aria-pressed="state.length === length"
                            @click="state.length = state.length === length ? null : length"
                        >
                            {{ length }}
                        </button>
                    </div>
                </div>
            </aside>

            <div class="md:col-span-9">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <p class="text-small text-mi-fil">{{ resultsLabel }}</p>
                    <MiSelect v-model="state.sort" :options="sortOptions" :label="t('collection.sort')" class="w-52" />
                </div>

                <div v-if="props.products.length > 0" class="mt-8 grid grid-cols-2 gap-x-5 gap-y-10 md:grid-cols-3 md:gap-x-8 md:gap-y-14">
                    <MiProductCard
                        v-for="(product, index) in props.products"
                        :key="product.id"
                        :product="product"
                        :eager="index < 3"
                    />
                </div>
                <p v-else class="mt-10 text-small text-mi-fil">{{ t('collection.empty') }}</p>

                <MiPagination :pagination="props.pagination" class="mt-12" />
            </div>
        </section>

        <!-- Contenu enrichi : replié sur téléphone, toujours dans le HTML. -->
        <section v-if="props.blocks.length > 0" class="mi-container border-t border-mi-ligne py-10 md:py-14">
            <MiDisclosure v-for="block in props.blocks" :key="block.title" :title="block.title">
                <p class="whitespace-pre-line">{{ block.body }}</p>
            </MiDisclosure>
        </section>

        <section v-if="props.faq.length > 0" class="mi-container border-t border-mi-ligne py-10 md:py-14">
            <h2 class="font-display text-h2 font-semibold leading-tight text-mi-indigo">{{ t('hub.faq') }}</h2>
            <div class="mt-6">
                <MiDisclosure v-for="entry in props.faq" :key="entry.question" :title="entry.question" as="h3">
                    <p class="whitespace-pre-line">{{ entry.answer }}</p>
                </MiDisclosure>
            </div>
        </section>

        <!-- Maillage sortant : aucune page du cluster ne reste orpheline. -->
        <section class="mi-container border-t border-mi-ligne py-10 md:py-14">
            <div class="grid grid-cols-1 gap-10 md:grid-cols-3 md:gap-8">
                <div v-if="props.links.washes.length > 0">
                    <h2 class="mi-caps text-mi-stone">{{ t('hub.washes') }}</h2>
                    <ul class="mt-4 flex flex-col gap-2">
                        <li v-for="link in props.links.washes" :key="link.url">
                            <Link :href="link.url" class="mi-link text-[16px] text-mi-indigo">{{ link.label }}</Link>
                        </li>
                    </ul>
                </div>

                <div v-if="props.links.sisters.length > 0">
                    <h2 class="mi-caps text-mi-stone">{{ t('hub.sisters') }}</h2>
                    <ul class="mt-4 flex flex-col gap-2">
                        <li v-for="link in props.links.sisters" :key="link.url">
                            <Link :href="link.url" class="mi-link text-[16px] text-mi-indigo">{{ link.label }}</Link>
                        </li>
                    </ul>
                </div>

                <div v-if="props.links.articles.length > 0">
                    <h2 class="mi-caps text-mi-stone">{{ t('hub.readMore') }}</h2>
                    <ul class="mt-4 flex flex-col gap-3">
                        <li v-for="link in props.links.articles" :key="link.url">
                            <Link :href="link.url" class="mi-link text-[16px] text-mi-indigo">{{ link.label }}</Link>
                            <p v-if="link.excerpt" class="mt-0.5 text-small text-mi-fil">{{ link.excerpt }}</p>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-10 flex flex-wrap items-center gap-4 border-t border-dashed border-mi-ocre pt-8">
                <div>
                    <p class="font-display text-[1.25rem] font-semibold text-mi-indigo">{{ t('hub.quiz') }}</p>
                    <p class="mt-1 text-small text-mi-fil">{{ t('hub.quizLead') }}</p>
                </div>
                <MiButton variant="outline" :href="props.links.quiz" arrow class="ms-auto">{{ t('hub.quiz') }}</MiButton>
            </div>
        </section>
    </StorefrontLayout>
</template>
