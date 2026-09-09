<script setup lang="ts">
import MiIcon, { type MiIconName } from '@/Components/mi/MiIcon.vue';
import { useI18n } from '@/composables/useI18n';
import type { TranslationKey } from '@/i18n';

/**
 * Barre de confiance : paiement à la livraison · échange offert 14 jours · livraison en boîte 24–48 h.
 */
const { t } = useI18n();

const items: ReadonlyArray<{ icon: MiIconName; key: TranslationKey }> = [
    { icon: 'cash', key: 'trust.cod' },
    { icon: 'exchange', key: 'trust.exchange' },
    { icon: 'box', key: 'trust.delivery' },
];
</script>

<template>
    <section class="border-y border-mi-ligne bg-mi-blanc">
        <ul class="mi-container grid grid-cols-1 gap-x-8 gap-y-3 py-5 text-small text-mi-charbon sm:grid-cols-3">
            <li
                v-for="item in items"
                :key="item.key"
                class="flex items-center justify-center gap-3"
            >
                <MiIcon :name="item.icon" :size="20" class="shrink-0 text-mi-stone" />
                <span>{{ t(item.key) }}</span>
            </li>
        </ul>
    </section>
</template>
