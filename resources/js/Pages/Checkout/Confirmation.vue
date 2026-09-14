<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import MiIcon, { type MiIconName } from '@/Components/mi/MiIcon.vue';
import MiOrderSummary from '@/Components/mi/MiOrderSummary.vue';
import { useI18n } from '@/composables/useI18n';
import { useMoney } from '@/composables/useMoney';
import { useRoute } from '@/composables/useRoute';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { ConfirmationPageProps } from '@/types/Checkout';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<ConfirmationPageProps>();

const { t } = useI18n();
const { format } = useMoney();
const route = useRoute();
const page = usePage();

const isCod = computed(() => props.order.payment_method === 'cod');
const hasBank = computed(() => props.bank.iban !== null && props.bank.iban !== '');
const isGuest = computed(() => page.props.auth.user === null);

const steps = computed<ReadonlyArray<{ icon: MiIconName; title: string; text: string }>>(() => [
    isCod.value
        ? { icon: 'cash', title: t('confirmation.codTitle'), text: t('confirmation.codText', { phone: props.order.address.phone, total: format(props.order.total) }) }
        : { icon: 'cash', title: t('confirmation.transferTitle'), text: t('confirmation.transferText', { total: format(props.order.total), number: props.order.number }) },
    { icon: 'box', title: t('confirmation.deliveryTitle'), text: t('confirmation.deliveryText', { zone: props.order.address.zone }) },
    { icon: 'exchange', title: t('confirmation.exchangeTitle'), text: t('confirmation.exchangeText') },
]);
</script>

<template>
    <Head>
        <title>{{ meta.title ?? '' }}</title>
    </Head>

    <StorefrontLayout>
        <section class="mi-container py-14 md:py-20">
            <p class="mi-caps text-mi-stone">{{ t('confirmation.kicker', { number: order.number }) }}</p>
            <h1 class="mt-4 max-w-3xl text-h1 md:text-[3.5rem] md:leading-[1.05]">{{ t('confirmation.title') }}</h1>
            <p class="mt-5 max-w-xl text-[17px] leading-relaxed text-mi-charbon/85">{{ t('confirmation.lead') }}</p>

            <div class="mt-12 grid grid-cols-1 gap-10 md:grid-cols-12 md:gap-12">
                <div class="flex flex-col gap-6 md:col-span-7">
                    <ol class="flex flex-col divide-y divide-mi-ligne border-y border-mi-ligne">
                        <li v-for="(step, index) in steps" :key="step.title" class="flex gap-5 py-6">
                            <span class="font-display text-h2 text-mi-stone/60" aria-hidden="true">0{{ index + 1 }}</span>
                            <div>
                                <h2 class="flex items-center gap-2.5 text-h3"><MiIcon :name="step.icon" :size="20" class="text-mi-stone" /> {{ step.title }}</h2>
                                <p class="mt-2 text-[15px] leading-relaxed text-mi-charbon/85">{{ step.text }}</p>

                                <dl v-if="index === 0 && !isCod" class="mt-4 grid grid-cols-[auto_1fr] gap-x-6 gap-y-1.5 border-2 border-dashed border-mi-ocre p-4 text-small">
                                    <template v-if="hasBank">
                                        <dt class="text-mi-fil">{{ t('confirmation.bankHolder') }}</dt><dd class="font-medium">{{ bank.holder }}</dd>
                                        <dt class="text-mi-fil">{{ t('confirmation.bankName') }}</dt><dd class="font-medium">{{ bank.bank }}</dd>
                                        <dt class="text-mi-fil">{{ t('confirmation.bankIban') }}</dt><dd class="font-medium tabular-nums">{{ bank.iban }}</dd>
                                    </template>
                                    <dd v-else class="col-span-2">{{ t('confirmation.bankSoon') }}</dd>
                                </dl>
                            </div>
                        </li>
                    </ol>

                    <div class="flex flex-wrap items-center gap-4">
                        <MiButton variant="outline" :href="route('collections.new')">{{ t('confirmation.continueShopping') }}</MiButton>
                        <Link v-if="isGuest" :href="route('register')" class="mi-link text-[15px] font-medium text-mi-stone">{{ t('confirmation.createAccount') }}</Link>
                    </div>
                </div>

                <aside class="md:col-span-5">
                    <div class="mi-card p-7">
                        <h2 class="text-h3">{{ t('confirmation.summary') }}</h2>
                        <div class="mt-5">
                            <MiOrderSummary
                                :lines="order.items"
                                :subtotal="order.subtotal"
                                :discount-total="order.discount_total"
                                :shipping-total="order.shipping_total"
                                :total="order.total"
                                :total-label="isCod ? t('checkout.codTotal') : t('cart.total')"
                            />
                        </div>

                        <h3 class="mi-caps mt-8 text-mi-fil">{{ t('confirmation.address') }}</h3>
                        <address class="mt-3 text-[15px] not-italic leading-relaxed text-mi-charbon">
                            <span class="font-medium">{{ order.address.name }}</span><br />
                            {{ order.address.line1 }}<br />
                            <template v-if="order.address.line2">{{ order.address.line2 }}<br /></template>
                            {{ order.address.city }}<span v-if="order.address.region"> · {{ order.address.region }}</span><br />
                            {{ order.address.phone }}
                        </address>
                    </div>
                </aside>
            </div>
        </section>
    </StorefrontLayout>
</template>
