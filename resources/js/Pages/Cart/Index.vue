<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import MiCartLine from '@/Components/mi/MiCartLine.vue';
import MiFreeShippingBar from '@/Components/mi/MiFreeShippingBar.vue';
import MiNotice from '@/Components/mi/MiNotice.vue';
import MiPrice from '@/Components/mi/MiPrice.vue';
import { useCart } from '@/composables/useCart';
import { useI18n } from '@/composables/useI18n';
import { useRoute } from '@/composables/useRoute';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { CartPageProps } from '@/types/Cart';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<CartPageProps>();

const { t } = useI18n();
const route = useRoute();
const page = usePage();
const { cart, isEmpty } = useCart();

const qtyError = computed(() => (page.props.errors as Record<string, string | undefined>).qty);
</script>

<template>
    <Head>
        <title>{{ meta.title ?? '' }}</title>
    </Head>

    <StorefrontLayout>
        <section class="mi-container py-12 md:py-16">
            <p class="mi-caps text-mi-stone">{{ t('brand.name') }}</p>
            <h1 class="mt-4 text-h1">{{ t('cart.title') }}</h1>

            <div v-if="isEmpty" class="mt-12 max-w-md">
                <p class="text-h3">{{ t('cart.empty') }}</p>
                <p class="mt-3 text-[15px] text-mi-fil">{{ t('cart.emptyHint') }}</p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <MiButton variant="primary" :href="route('collections.new')" arrow>{{ t('cart.continue') }}</MiButton>
                    <MiButton variant="ghost" :href="route('size-quiz')">{{ t('nav.sizeQuiz') }}</MiButton>
                </div>
            </div>

            <div v-else class="mt-10 grid grid-cols-1 gap-10 md:grid-cols-12 md:gap-12">
                <div class="md:col-span-7">
                    <MiNotice v-if="qtyError" kind="error" class="mb-6">{{ qtyError }}</MiNotice>
                    <ul class="divide-y divide-mi-ligne border-y border-mi-ligne">
                        <li v-for="line in cart.items" :key="line.id">
                            <MiCartLine :line="line" />
                        </li>
                    </ul>
                    <MiButton variant="ghost" :href="route('collections.new')" class="mt-6">{{ t('cart.continue') }}</MiButton>
                </div>

                <aside class="md:col-span-5">
                    <div class="mi-card p-7 md:sticky md:top-28">
                        <MiFreeShippingBar :threshold="cart.free_shipping_threshold" :remaining="cart.free_shipping_remaining" />

                        <dl class="mt-6 flex flex-col gap-3 text-[15px]">
                            <div class="flex items-center justify-between">
                                <dt>{{ t('cart.subtotal') }}</dt>
                                <dd><MiPrice :amount="cart.subtotal" /></dd>
                            </div>
                            <div v-if="cart.discount.total > 0" class="flex items-center justify-between text-mi-vert">
                                <dt>{{ cart.discount.label ?? t('cart.discount') }}</dt>
                                <dd class="tabular-nums font-semibold">− <MiPrice :amount="cart.discount.total" class="[&_span]:text-mi-vert" /></dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt>{{ t('cart.shipping') }}</dt>
                                <dd class="text-small text-mi-fil">{{ t('cart.shippingLater') }}</dd>
                            </div>
                            <div class="mi-stitch mt-2 flex items-baseline justify-between pt-4">
                                <dt class="font-medium">{{ t('cart.total') }} <span class="text-small font-normal text-mi-fil">· {{ t('cart.totalHint') }}</span></dt>
                                <dd><MiPrice :amount="cart.total" display size="lg" /></dd>
                            </div>
                        </dl>

                        <p v-if="cart.discount.total === 0 && cart.count === 1" class="mt-4 text-small text-mi-fil">{{ t('cart.bundleHint') }}</p>

                        <MiButton variant="primary" block :href="route('checkout.show')" class="mt-7" arrow>{{ t('cart.checkout') }}</MiButton>
                        <p class="mt-4 text-center text-small text-mi-fil">{{ t('checkout.secure') }}</p>
                    </div>
                </aside>
            </div>
        </section>
    </StorefrontLayout>
</template>
