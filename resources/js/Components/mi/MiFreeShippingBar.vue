<script setup lang="ts">
import { useI18n } from '@/composables/useI18n';
import { useMoney } from '@/composables/useMoney';
import { computed } from 'vue';

/**
 * Barre « livraison offerte » : progression fine indigo sur fond ligne.
 */
const props = defineProps<{
    threshold: number | null;
    remaining: number | null;
}>();

const { t } = useI18n();
const { format } = useMoney();

const reached = computed(() => props.remaining !== null && props.remaining <= 0);
const progress = computed(() => {
    if (props.threshold === null || props.threshold <= 0 || props.remaining === null) return 0;
    return Math.min(100, Math.round(((props.threshold - props.remaining) / props.threshold) * 100));
});
</script>

<template>
    <div v-if="threshold !== null" class="flex flex-col gap-2">
        <p class="text-small" :class="reached ? 'font-medium text-mi-vert' : 'text-mi-charbon'">
            {{ reached ? t('cart.freeShippingReached') : t('cart.freeShippingRemaining', { amount: format(remaining ?? 0) }) }}
        </p>
        <div class="h-0.5 w-full bg-mi-ligne" role="progressbar" :aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100">
            <div class="h-full transition-[width] duration-200" :class="reached ? 'bg-mi-vert' : 'bg-mi-indigo'" :style="{ width: `${progress}%` }"></div>
        </div>
    </div>
</template>
