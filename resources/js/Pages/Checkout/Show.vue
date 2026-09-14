<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import MiCheckbox from '@/Components/mi/MiCheckbox.vue';
import MiInput from '@/Components/mi/MiInput.vue';
import MiNotice from '@/Components/mi/MiNotice.vue';
import MiOrderSummary from '@/Components/mi/MiOrderSummary.vue';
import MiRadioCard from '@/Components/mi/MiRadioCard.vue';
import MiTextarea from '@/Components/mi/MiTextarea.vue';
import { useCart } from '@/composables/useCart';
import { useI18n } from '@/composables/useI18n';
import { useRoute } from '@/composables/useRoute';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { CheckoutPageProps, ShippingZoneOption } from '@/types/Checkout';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, useId } from 'vue';

const props = defineProps<CheckoutPageProps>();

const { t, tc } = useI18n();
const route = useRoute();
const { cart } = useCart();

const form = useForm({
    name: props.prefill.name,
    phone: props.prefill.phone,
    email: props.prefill.email,
    line1: props.prefill.line1,
    line2: props.prefill.line2,
    city: props.prefill.city,
    region: props.prefill.region,
    payment_method: 'cod',
    discount_code: cart.value.discount.code ?? '',
    notes: '',
    accept_terms: false,
});

const citiesListId = useId();

/* Estimation de livraison côté client, recalculée par le serveur à la validation. */
const normalize = (value: string): string =>
    value
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '')
        .trim()
        .toLowerCase();

const zone = computed<ShippingZoneOption | null>(() => {
    const city = normalize(form.city);
    if (city === '') return null;
    const match = props.zones.find((z) => z.cities.some((known) => normalize(known) === city));
    return match ?? props.zones.find((z) => z.cities.length === 0) ?? null;
});

const shippingCost = computed<number | null>(() => {
    if (zone.value === null) return null;
    if (zone.value.free_threshold !== null && cart.value.total >= zone.value.free_threshold) return 0;
    return zone.value.price;
});

const shippingNote = computed(() =>
    zone.value === null ? t('checkout.shippingUnknown') : t('checkout.shippingZone', { zone: zone.value.name, delay: zone.value.delay }),
);

const total = computed(() => cart.value.total + (shippingCost.value ?? 0));

const applyCode = (): void => {
    router.get(route('checkout.show'), { code: form.discount_code }, { preserveState: true, preserveScroll: true, only: ['cart'], replace: true });
};

const submit = (): void => {
    form.post(route('checkout.store'));
};
</script>

<template>
    <Head>
        <title>{{ meta.title ?? '' }}</title>
    </Head>

    <StorefrontLayout>
        <section class="mi-container py-12 md:py-16">
            <p class="mi-caps text-mi-stone">{{ t('brand.name') }}</p>
            <h1 class="mt-4 text-h1">{{ t('checkout.title') }}</h1>
            <p class="mt-4 max-w-xl text-[17px] leading-relaxed text-mi-charbon/85">{{ t('checkout.lead') }}</p>

            <form class="mt-10 grid grid-cols-1 gap-10 md:grid-cols-12 md:gap-12" novalidate @submit.prevent="submit">
                <div class="flex flex-col gap-10 md:col-span-7">
                    <!-- Livraison -->
                    <section class="mi-card p-7 md:p-9">
                        <h2 class="text-h3"><span class="text-mi-stone">01</span> {{ t('checkout.stepAddress') }}</h2>
                        <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <MiInput v-model="form.name" name="name" :label="t('checkout.name')" :error="form.errors.name" autocomplete="name" required class="sm:col-span-2" />
                            <MiInput v-model="form.phone" type="tel" name="phone" :label="t('checkout.phone')" :hint="t('checkout.phoneHint')" :error="form.errors.phone" autocomplete="tel" placeholder="06 12 34 56 78" required />
                            <MiInput v-model="form.email" type="email" name="email" :label="t('checkout.email')" :hint="t('checkout.emailHint')" :error="form.errors.email" autocomplete="email" />
                            <MiInput v-model="form.line1" name="line1" :label="t('checkout.line1')" :error="form.errors.line1" autocomplete="address-line1" required class="sm:col-span-2" />
                            <MiInput v-model="form.line2" name="line2" :label="t('checkout.line2')" :error="form.errors.line2" autocomplete="address-line2" class="sm:col-span-2" />
                            <div class="flex flex-col gap-2">
                                <div class="flex items-baseline justify-between gap-4">
                                    <label :for="`${citiesListId}-ville`" class="text-small font-medium text-mi-charbon">{{ t('checkout.city') }}</label>
                                </div>
                                <input
                                    :id="`${citiesListId}-ville`"
                                    v-model="form.city"
                                    name="city"
                                    type="text"
                                    :list="citiesListId"
                                    autocomplete="address-level2"
                                    required
                                    class="mi-input"
                                    :aria-invalid="form.errors.city ? 'true' : undefined"
                                />
                                <datalist :id="citiesListId">
                                    <option v-for="city in cities" :key="city" :value="city"></option>
                                </datalist>
                                <p class="text-small text-mi-fil">{{ t('checkout.cityHint') }}</p>
                                <p v-if="form.errors.city" class="text-small text-mi-erreur" role="alert">{{ form.errors.city }}</p>
                            </div>
                            <MiInput v-model="form.region" name="region" :label="t('checkout.region')" :error="form.errors.region" autocomplete="address-level1" />
                        </div>
                    </section>

                    <!-- Paiement -->
                    <section class="mi-card p-7 md:p-9">
                        <h2 class="text-h3"><span class="text-mi-stone">02</span> {{ t('checkout.stepPayment') }}</h2>
                        <div class="mt-6 grid grid-cols-1 gap-3">
                            <MiRadioCard
                                v-for="method in payment_methods"
                                :key="method.value"
                                v-model="form.payment_method"
                                name="payment_method"
                                :value="method.value"
                                :label="method.label"
                                :description="method.description"
                            />
                        </div>
                        <p v-if="form.errors.payment_method" class="mt-2 text-small text-mi-erreur" role="alert">{{ form.errors.payment_method }}</p>

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-end">
                            <MiInput v-model="form.discount_code" name="discount_code" :label="t('checkout.discountCode')" :error="form.errors.discount_code ?? cart.discount.code_error ?? undefined" class="flex-1" />
                            <MiButton variant="outline" @click="applyCode">{{ t('checkout.discountApply') }}</MiButton>
                        </div>

                        <div class="mt-8">
                            <MiTextarea v-model="form.notes" name="notes" :label="t('checkout.notes')" :hint="t('checkout.notesHint')" :error="form.errors.notes" :maxlength="500" />
                        </div>
                    </section>
                </div>

                <!-- Récapitulatif -->
                <aside class="md:col-span-5">
                    <div class="mi-card p-7 md:sticky md:top-28">
                        <div class="flex items-baseline justify-between">
                            <h2 class="text-h3">{{ t('checkout.stepSummary') }}</h2>
                            <Link :href="route('cart.index')" class="mi-link text-small text-mi-stone">{{ t('checkout.editCart') }}</Link>
                        </div>
                        <p class="mt-1 text-small text-mi-fil">{{ tc('checkout.itemsCount', 'checkout.itemsCountPlural', cart.count) }}</p>

                        <div class="mt-6">
                            <MiOrderSummary
                                :lines="cart.items"
                                :subtotal="cart.subtotal"
                                :discount-total="cart.discount.total"
                                :discount-label="cart.discount.label"
                                :shipping-total="shippingCost"
                                :shipping-note="shippingNote"
                                :total="total"
                                :total-label="form.payment_method === 'cod' ? t('checkout.codTotal') : t('cart.total')"
                            />
                        </div>

                        <div class="mt-7">
                            <MiCheckbox v-model="form.accept_terms" name="accept_terms" :label="t('checkout.terms')" />
                            <p v-if="form.errors.accept_terms" class="mt-2 text-small text-mi-erreur" role="alert">{{ form.errors.accept_terms }}</p>
                        </div>

                        <MiNotice v-if="Object.keys(form.errors).length > 0" kind="error" class="mt-5">
                            {{ Object.values(form.errors)[0] }}
                        </MiNotice>

                        <MiButton type="submit" variant="primary" block class="mt-6" :loading="form.processing" arrow>
                            {{ form.processing ? t('checkout.submitting') : t('checkout.submit') }}
                        </MiButton>
                        <p class="mt-4 text-center text-small text-mi-fil">{{ t('checkout.secure') }}</p>
                    </div>
                </aside>
            </form>
        </section>
    </StorefrontLayout>
</template>
