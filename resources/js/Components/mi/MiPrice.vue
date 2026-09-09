<script setup lang="ts">
import { useI18n } from '@/composables/useI18n';
import { useMoney } from '@/composables/useMoney';
import { computed } from 'vue';

/**
 * Prix en centimes entiers, formaté « 499,00 dh » en chiffres tabulaires.
 * `compareAt` n'est affiché que dans le gabarit vente privée.
 */
type Size = 'sm' | 'md' | 'lg';

const props = withDefaults(
    defineProps<{
        amount: number;
        compareAt?: number | null;
        size?: Size;
        /** Cormorant Garamond pour un prix mis en avant. */
        display?: boolean;
    }>(),
    {
        compareAt: null,
        size: 'md',
        display: false,
    },
);

const { format } = useMoney();
const { t } = useI18n();

const sizeClass: Record<Size, string> = {
    sm: 'text-small',
    md: 'text-body',
    lg: 'text-h3',
};

const formatted = computed(() => format(props.amount));
const formattedCompareAt = computed(() =>
    props.compareAt !== null && props.compareAt > props.amount ? format(props.compareAt) : null,
);
</script>

<template>
    <span class="inline-flex items-baseline gap-2.5 tabular-nums" :class="sizeClass[size]">
        <span
            class="font-semibold text-mi-charbon"
            :class="display ? 'font-display' : 'font-body'"
        >
            {{ formatted }}
        </span>
        <s v-if="formattedCompareAt" class="font-normal text-mi-fil">
            <span class="sr-only">{{ t('price.was') }} </span>{{ formattedCompareAt }}
        </s>
    </span>
</template>
