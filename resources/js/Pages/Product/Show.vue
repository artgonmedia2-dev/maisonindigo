<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import MiGallery from '@/Components/mi/MiGallery.vue';
import MiIcon, { type MiIconName } from '@/Components/mi/MiIcon.vue';
import MiInput from '@/Components/mi/MiInput.vue';
import MiNotice from '@/Components/mi/MiNotice.vue';
import MiPatch from '@/Components/mi/MiPatch.vue';
import MiBreadcrumb from '@/Components/mi/MiBreadcrumb.vue';
import MiPrice from '@/Components/mi/MiPrice.vue';
import MiProductCard from '@/Components/mi/MiProductCard.vue';
import MiSizeSelector from '@/Components/mi/MiSizeSelector.vue';
import { useCart } from '@/composables/useCart';
import { useI18n } from '@/composables/useI18n';
import { useRoute } from '@/composables/useRoute';
import type { TranslationKey } from '@/i18n';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { ProductPageProps } from '@/types/Catalog';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<ProductPageProps>();

const { t } = useI18n();
const route = useRoute();
const page = usePage();
const { add, busy } = useCart();

const size = ref<number | null>(null);
const length = ref<number | null>(props.product.lengths.includes(32) ? 32 : (props.product.lengths[0] ?? null));

const selectedVariant = computed(() =>
    props.product.variants.find((v) => v.size === size.value && v.length === length.value),
);
const canAdd = computed(() => selectedVariant.value !== undefined && selectedVariant.value.in_stock && !busy.value);
const isOutOfStock = computed(() => selectedVariant.value !== undefined && !selectedVariant.value.in_stock);

const addError = computed(() => {
    const errors = page.props.errors as Record<string, string | undefined>;
    return errors.variant_id;
});

const addToCart = (): void => {
    if (selectedVariant.value !== undefined && selectedVariant.value.in_stock) {
        add(selectedVariant.value.id, 1);
    }
};

/* « Me prévenir » */
const alertForm = useForm({ variant_id: 0, email: '' });
const alertSent = ref(false);

const submitAlert = (): void => {
    if (selectedVariant.value === undefined) return;
    alertForm.variant_id = selectedVariant.value.id;
    alertForm.post(route('stock-alerts.store'), {
        preserveScroll: true,
        onSuccess: () => {
            alertSent.value = true;
            alertForm.reset('email');
        },
    });
};


const reassurance: ReadonlyArray<{ icon: MiIconName; key: TranslationKey }> = [
    { icon: 'exchange', key: 'product.exchange' },
    { icon: 'box', key: 'product.delivery' },
    { icon: 'cash', key: 'product.cod' },
];

const weightLabel = computed(() => (props.product.weight_oz === null ? null : `${String(props.product.weight_oz).replace('.', ',')} oz`));
const modelHeight = computed(() => (props.product.model_height_cm === null ? null : (props.product.model_height_cm / 100).toFixed(2).replace('.', ',')));
</script>

<template>
    <Head>
        <title>{{ meta.title ?? product.title }}</title>
        <meta v-if="meta.description" name="description" :content="meta.description" />
    </Head>

    <StorefrontLayout>
        <MiBreadcrumb :items="props.breadcrumb" />

        <section class="mi-container grid grid-cols-1 gap-10 py-8 md:grid-cols-12 md:gap-12 md:py-12">
            <div class="md:col-span-7">
                <MiGallery :images="product.images" :title="product.title" :gender="product.gender" />
            </div>

            <div class="md:col-span-5">
                <div class="md:sticky md:top-28 flex flex-col gap-7">
                    <header>
                        <div class="flex items-center gap-3">
                            <MiPatch v-if="product.patch" :kind="product.patch" />
                            <p class="mi-caps text-mi-stone">{{ product.gender_label }}</p>
                        </div>
                        <h1 class="mt-3 text-h1">{{ product.title }}</h1>
                        <p class="mt-2 text-[15px] text-mi-fil">{{ product.subtitle }}</p>
                        <p class="mt-2 text-[15px] text-mi-fil">{{ product.subtitle }}</p>
                        <div class="mt-4">
                            <MiPrice :amount="product.price" :compare-at="product.compare_at_price" display size="lg" />
                        </div>
                    </header>

                    <hr class="mi-stitch border-0" />

                    <MiSizeSelector v-model:size="size" v-model:length="length" :sizes="product.sizes" :lengths="product.lengths" :variants="product.variants">
                        <template #size-aside>
                            <Link :href="route('size-quiz')" class="mi-link inline-flex items-center gap-1.5 text-small font-medium text-mi-stone">
                                <MiIcon name="ruler" :size="16" /> {{ t('product.findMySize') }}
                            </Link>
                        </template>
                    </MiSizeSelector>

                    <MiNotice v-if="addError" kind="error">{{ addError }}</MiNotice>

                    <div v-if="isOutOfStock" class="mi-card p-5">
                        <p class="text-[15px] font-medium text-mi-charbon">{{ t('product.notify') }}</p>
                        <p class="mt-1 text-small text-mi-fil">{{ t('product.notifyLead', { size: `${size} / ${length}` }) }}</p>
                        <MiNotice v-if="alertSent" kind="success" class="mt-4">{{ page.props.flash.success }}</MiNotice>
                        <form v-else class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end" @submit.prevent="submitAlert">
                            <MiInput v-model="alertForm.email" type="email" name="email" :label="t('auth.email')" :error="alertForm.errors.email" autocomplete="email" required class="flex-1" />
                            <MiButton type="submit" variant="outline" :loading="alertForm.processing">{{ t('product.notifySend') }}</MiButton>
                        </form>
                    </div>

                    <MiButton v-else variant="primary" block :disabled="!canAdd" :loading="busy" @click="addToCart">
                        {{ busy ? t('product.adding') : t('product.addToCart') }}
                    </MiButton>

                    <ul class="flex flex-col gap-2.5 text-small text-mi-charbon">
                        <li v-for="item in reassurance" :key="item.key" class="flex items-center gap-3">
                            <MiIcon :name="item.icon" :size="18" class="shrink-0 text-mi-stone" />
                            {{ t(item.key) }}
                        </li>
                    </ul>

                    <div class="divide-y divide-mi-ligne border-y border-mi-ligne">
                        <details class="group py-4" open>
                            <summary class="flex cursor-pointer list-none items-center justify-between text-[15px] font-medium text-mi-charbon">
                                {{ t('product.details') }}
                                <MiIcon name="chevron" :size="18" class="text-mi-fil transition-transform duration-150 group-open:rotate-180" />
                            </summary>
                            <div class="mt-4 flex flex-col gap-3 text-[15px] leading-relaxed text-mi-charbon/85">
                                <p v-if="product.description">{{ product.description }}</p>
                                <p v-if="props.hub" class="mt-4">
                                    <Link :href="props.hub.url" class="mi-link text-mi-stone">{{ props.hub.label }}</Link>
                                </p>
                                <dl class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-1.5 text-small">
                                    <template v-if="product.fabric_origin"><dt class="text-mi-fil">{{ t('product.fabric') }}</dt><dd>{{ product.fabric_origin }}</dd></template>
                                    <template v-if="weightLabel"><dt class="text-mi-fil">{{ t('product.weight') }}</dt><dd>{{ weightLabel }}</dd></template>
                                    <template v-if="product.composition"><dt class="text-mi-fil">{{ t('product.composition') }}</dt><dd>{{ product.composition }}</dd></template>
                                </dl>
                                <p v-if="modelHeight && product.model_size" class="text-small text-mi-fil">
                                    {{ t('product.model', { height: modelHeight, size: product.model_size }) }}
                                </p>
                                <p v-if="product.size_advice" class="text-small font-medium text-mi-charbon">{{ product.size_advice }}</p>
                            </div>
                        </details>

                        <details v-if="product.size_chart.length > 0" class="group py-4">
                            <summary class="flex cursor-pointer list-none items-center justify-between text-[15px] font-medium text-mi-charbon">
                                {{ t('product.measurements') }}
                                <MiIcon name="chevron" :size="18" class="text-mi-fil transition-transform duration-150 group-open:rotate-180" />
                            </summary>
                            <p class="mt-3 text-small text-mi-fil">{{ t('product.measurementsHint') }}</p>
                            <div class="mt-3 overflow-x-auto">
                                <table class="w-full text-small tabular-nums">
                                    <thead>
                                        <tr class="border-b border-mi-ligne text-start text-mi-fil">
                                            <th class="py-2 pe-3 text-start font-medium">{{ t('product.chartSize') }}</th>
                                            <th class="py-2 pe-3 text-start font-medium">{{ t('product.chartWaist') }}</th>
                                            <th class="py-2 pe-3 text-start font-medium">{{ t('product.chartHips') }}</th>
                                            <th class="py-2 pe-3 text-start font-medium">{{ t('product.chartThigh') }}</th>
                                            <th class="py-2 pe-3 text-start font-medium">{{ t('product.chartInseam', { length: 30 }) }}</th>
                                            <th class="py-2 pe-3 text-start font-medium">{{ t('product.chartInseam', { length: 32 }) }}</th>
                                            <th class="py-2 text-start font-medium">{{ t('product.chartInseam', { length: 34 }) }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in product.size_chart" :key="row.size" class="border-b border-mi-ligne" :class="row.size === size ? 'bg-mi-ecru font-medium' : ''">
                                            <td class="py-2 pe-3">{{ row.size }}</td>
                                            <td class="py-2 pe-3">{{ row.waist_cm }}</td>
                                            <td class="py-2 pe-3">{{ row.hips_cm }}</td>
                                            <td class="py-2 pe-3">{{ row.thigh_cm }}</td>
                                            <td class="py-2 pe-3">{{ row.inseam_30 }}</td>
                                            <td class="py-2 pe-3">{{ row.inseam_32 }}</td>
                                            <td class="py-2">{{ row.inseam_34 }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </details>

                        <details class="group py-4">
                            <summary class="flex cursor-pointer list-none items-center justify-between text-[15px] font-medium text-mi-charbon">
                                {{ t('product.care') }}
                                <MiIcon name="chevron" :size="18" class="text-mi-fil transition-transform duration-150 group-open:rotate-180" />
                            </summary>
                            <p class="mt-4 text-[15px] leading-relaxed text-mi-charbon/85">{{ t('product.careText') }}</p>
                        </details>
                    </div>
                </div>
            </div>
        </section>

        <section v-if="related.length > 0" class="border-t border-mi-ligne">
            <div class="mi-container py-14 md:py-20">
                <h2 class="text-h2">{{ t('product.completeLook') }}</h2>
                <div class="mt-8 grid grid-cols-2 gap-x-5 gap-y-10 md:grid-cols-3 md:gap-x-8">
                    <MiProductCard v-for="item in related" :key="item.id" :product="item" />
                </div>
            </div>
        </section>
    </StorefrontLayout>
</template>
