<script setup lang="ts">
import MiIcon, { type MiIconName } from '@/Components/mi/MiIcon.vue';
import { useI18n } from '@/composables/useI18n';
import type { TranslationKey } from '@/i18n';

/**
 * Barre de confiance : paiement à la livraison · échange offert 14 jours · livraison en boîte 24–48 h.
 */
const { t } = useI18n();

interface TrustItem {
    icon: MiIconName;
    key: TranslationKey;
    desc: string;
}

const items: ReadonlyArray<TrustItem> = [
    { icon: 'cash', key: 'trust.cod', desc: 'Règlement en espèces à la réception' },
    { icon: 'exchange', key: 'trust.exchange', desc: 'Nouvelle taille envoyée sans aucun frais' },
    { icon: 'box', key: 'trust.delivery', desc: 'Expédition soignée dans toutes les villes' },
];
</script>

<template>
    <section class="border-y border-mi-ligne bg-gradient-to-b from-mi-blanc via-mi-ecru/40 to-mi-blanc py-6 md:py-8">
        <div class="mi-container">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 md:gap-6">
                <div
                    v-for="item in items"
                    :key="item.key"
                    class="group flex items-start gap-4 border border-mi-ligne/70 bg-mi-blanc p-4 transition-all duration-200 hover:border-mi-indigo/30 hover:shadow-sm sm:items-center sm:p-5"
                >
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center bg-mi-indigo/5 text-mi-indigo transition-colors duration-200 group-hover:bg-mi-indigo group-hover:text-mi-ecru">
                        <MiIcon :name="item.icon" :size="22" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-display text-[1.0625rem] font-semibold leading-snug text-mi-indigo">
                            {{ t(item.key) }}
                        </p>
                        <p class="mt-0.5 text-[13px] text-mi-fil">
                            {{ item.desc }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
