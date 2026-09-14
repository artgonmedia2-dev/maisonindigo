<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import MiCheckbox from '@/Components/mi/MiCheckbox.vue';
import MiIcon from '@/Components/mi/MiIcon.vue';
import MiPagination from '@/Components/mi/MiPagination.vue';
import MiProductCard from '@/Components/mi/MiProductCard.vue';
import MiSelect from '@/Components/mi/MiSelect.vue';
import { useI18n } from '@/composables/useI18n';
import { useRoute } from '@/composables/useRoute';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { CollectionFilters, CollectionPageProps } from '@/types/Catalog';
import { Head, router } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';

const props = defineProps<CollectionPageProps>();

const { t, tc } = useI18n();
const route = useRoute();

const routeName = computed(() => `collections.${props.handle}`);

const local = reactive<CollectionFilters>({
    cut: [...props.filters.cut],
    wash: [...props.filters.wash],
    size: props.filters.size,
    sort: props.filters.sort,
});

watch(
    () => props.filters,
    (filters) => {
        local.cut = [...filters.cut];
        local.wash = [...filters.wash];
        local.size = filters.size;
        local.sort = filters.sort;
    },
);

const apply = (): void => {
    router.get(
        route(routeName.value),
        {
            cut: local.cut.length > 0 ? local.cut : undefined,
            wash: local.wash.length > 0 ? local.wash : undefined,
            size: local.size ?? undefined,
            sort: local.sort !== 'new' ? local.sort : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const toggle = (list: string[], value: string): void => {
    const index = list.indexOf(value);
    if (index === -1) list.push(value);
    else list.splice(index, 1);
    apply();
};

const selectSize = (size: number | null): void => {
    local.size = local.size === size ? null : size;
    apply();
};

const reset = (): void => {
    local.cut = [];
    local.wash = [];
    local.size = null;
    local.sort = 'new';
    apply();
};

const hasFilters = computed(() => local.cut.length > 0 || local.wash.length > 0 || local.size !== null);

const filtersOpen = ref(false);

const isChecked = (list: string[], value: string): boolean => list.includes(value);
</script>

<template>
    <Head>
        <title>{{ meta.title ?? '' }}</title>
        <meta v-if="meta.description" name="description" :content="meta.description" />
    </Head>

    <StorefrontLayout>
        <section class="mi-container pt-12 md:pt-16">
            <p class="mi-caps text-mi-stone">{{ t('brand.name') }}</p>
            <h1 class="mt-4 text-h1 md:text-[3.5rem] md:leading-[1.05]">{{ heading }}</h1>
            <p class="mt-5 max-w-2xl text-[17px] leading-relaxed text-mi-charbon/85">{{ lead }}</p>
        </section>

        <section class="mi-container grid grid-cols-1 gap-10 py-10 md:grid-cols-12 md:gap-10 md:py-14">
            <!-- Filtres -->
            <aside class="md:col-span-3">
                <div class="flex items-center justify-between md:hidden">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 border-2 border-mi-indigo px-4 py-2.5 text-small font-semibold text-mi-indigo"
                        :aria-expanded="filtersOpen"
                        aria-controls="filtres"
                        @click="filtersOpen = !filtersOpen"
                    >
                        <MiIcon name="menu" :size="16" /> {{ filtersOpen ? t('collection.close') : t('collection.open') }}
                    </button>
                    <p class="text-small text-mi-fil">{{ tc('collection.results', 'collection.resultsPlural', pagination.total) }}</p>
                </div>

                <div id="filtres" class="mt-6 flex flex-col gap-8 md:sticky md:top-28 md:mt-0" :class="filtersOpen ? '' : 'hidden md:flex'">
                    <div class="hidden items-center justify-between md:flex">
                        <h2 class="mi-caps text-mi-charbon">{{ t('collection.filters') }}</h2>
                        <button v-if="hasFilters" type="button" class="mi-link text-small text-mi-stone" @click="reset">{{ t('collection.reset') }}</button>
                    </div>

                    <fieldset>
                        <legend class="text-small font-medium text-mi-charbon">{{ t('collection.cut') }}</legend>
                        <div class="mt-3 flex flex-col gap-2.5">
                            <MiCheckbox
                                v-for="option in options.cuts"
                                :key="option.value"
                                :label="option.label"
                                :model-value="isChecked(local.cut, option.value)"
                                @update:model-value="toggle(local.cut, option.value)"
                            />
                        </div>
                    </fieldset>

                    <fieldset v-if="options.washes.length > 0">
                        <legend class="text-small font-medium text-mi-charbon">{{ t('collection.wash') }}</legend>
                        <div class="mt-3 flex flex-col gap-2.5">
                            <MiCheckbox
                                v-for="option in options.washes"
                                :key="option.value"
                                :label="option.label"
                                :model-value="isChecked(local.wash, option.value)"
                                @update:model-value="toggle(local.wash, option.value)"
                            />
                        </div>
                    </fieldset>

                    <fieldset v-if="options.sizes.length > 0">
                        <legend class="text-small font-medium text-mi-charbon">{{ t('collection.size') }}</legend>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button
                                v-for="size in options.sizes"
                                :key="size"
                                type="button"
                                class="flex h-10 w-11 items-center justify-center border-[1.5px] text-[13px] font-medium transition-colors duration-150"
                                :class="local.size === size ? 'border-mi-indigo bg-mi-indigo text-mi-blanc' : 'border-[#cfcac0] bg-mi-blanc text-mi-charbon hover:border-mi-indigo'"
                                :aria-pressed="local.size === size"
                                @click="selectSize(size)"
                            >
                                {{ size }}
                            </button>
                        </div>
                    </fieldset>

                    <button v-if="hasFilters" type="button" class="mi-link self-start text-small text-mi-stone md:hidden" @click="reset">{{ t('collection.reset') }}</button>
                </div>
            </aside>

            <!-- Grille -->
            <div class="md:col-span-9">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-mi-ligne pb-4">
                    <p class="hidden text-small text-mi-fil md:block">{{ tc('collection.results', 'collection.resultsPlural', pagination.total) }}</p>
                    <div class="w-full sm:w-56">
                        <MiSelect v-model="local.sort" :label="t('collection.sort')" :options="options.sorts" hide-label @update:model-value="apply" />
                    </div>
                </div>

                <div v-if="products.length === 0" class="py-20 text-center">
                    <p class="text-h3">{{ t('collection.empty') }}</p>
                    <p class="mt-3 text-[15px] text-mi-fil">{{ t('collection.emptyHint') }}</p>
                    <MiButton variant="outline" class="mt-8" @click="reset">{{ t('collection.reset') }}</MiButton>
                </div>

                <div v-else class="mt-8 grid grid-cols-2 gap-x-5 gap-y-10 md:grid-cols-3 md:gap-x-8 md:gap-y-14">
                    <MiProductCard v-for="(product, index) in products" :key="product.id" :product="product" :eager="index < 3" />
                </div>

                <div class="mt-14">
                    <MiPagination :pagination="pagination" />
                </div>
            </div>
        </section>
    </StorefrontLayout>
</template>
