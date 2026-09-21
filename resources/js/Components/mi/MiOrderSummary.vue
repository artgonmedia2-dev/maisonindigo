<script setup lang="ts">
import MiPrice from '@/Components/mi/MiPrice.vue';
import { useI18n } from '@/composables/useI18n';

/**
 * Récapitulatif des lignes et des totaux, pour la caisse et la confirmation.
 * Montants en centimes.
 */
export interface SummaryLine {
    id: number;
    title: string;
    size: number;
    length: number;
    qty: number;
    unit_price: number;
    total: number;
}

withDefaults(
    defineProps<{
        lines: SummaryLine[];
        subtotal: number;
        discountTotal: number;
        discountLabel?: string | null;
        /** Nul : livraison inconnue (ville non saisie). */
        shippingTotal: number | null;
        shippingNote?: string | null;
        total: number;
        totalLabel?: string;
    }>(),
    { discountLabel: null, shippingNote: null, totalLabel: undefined },
);

const { t } = useI18n();
</script>

<template>
    <div>
        <ul class="divide-y divide-mi-ligne border-y border-mi-ligne">
            <li v-for="line in lines" :key="line.id" class="flex flex-wrap items-start justify-between gap-x-4 gap-y-1 py-3.5 text-[15px]">
                <div class="min-w-[7.5rem] flex-1">
                    <p class="font-medium text-mi-charbon">{{ line.title }}</p>
                    <p class="text-small text-mi-fil">{{ line.size }} / {{ line.length }} · × {{ line.qty }}</p>
                </div>
                <MiPrice :amount="line.total" class="shrink-0" />
            </li>
        </ul>

        <dl class="mt-4 flex flex-col gap-2.5 text-[15px]">
            <div class="flex items-center justify-between">
                <dt class="text-mi-charbon">{{ t('cart.subtotal') }}</dt>
                <dd><MiPrice :amount="subtotal" /></dd>
            </div>
            <div v-if="discountTotal > 0" class="flex items-center justify-between text-mi-vert">
                <dt>{{ discountLabel ?? t('cart.discount') }}</dt>
                <dd class="tabular-nums font-semibold">− <MiPrice :amount="discountTotal" class="[&_span]:text-mi-vert" /></dd>
            </div>
            <div class="flex items-center justify-between">
                <dt class="text-mi-charbon">
                    {{ t('cart.shipping') }}
                    <span v-if="shippingNote" class="block text-small text-mi-fil">{{ shippingNote }}</span>
                </dt>
                <dd>
                    <span v-if="shippingTotal === null" class="text-small text-mi-fil">—</span>
                    <span v-else-if="shippingTotal === 0" class="font-semibold text-mi-vert">{{ t('checkout.shippingFree') }}</span>
                    <MiPrice v-else :amount="shippingTotal" />
                </dd>
            </div>
            <div class="mi-stitch mt-2 flex items-baseline justify-between pt-4">
                <dt class="font-medium text-mi-charbon">{{ totalLabel ?? t('cart.total') }}</dt>
                <dd><MiPrice :amount="total" display size="lg" /></dd>
            </div>
        </dl>
    </div>
</template>
