<script setup lang="ts">
import MiIcon from '@/Components/mi/MiIcon.vue';
import { useI18n } from '@/composables/useI18n';

/**
 * Compteur de quantité : moins, valeur, plus. Borné par le stock (5 maximum par ligne).
 */
const props = withDefaults(
    defineProps<{
        min?: number;
        max?: number;
        disabled?: boolean;
        /** Petit format pour le tiroir. */
        compact?: boolean;
    }>(),
    { min: 1, max: 5, disabled: false, compact: false },
);

const model = defineModel<number>({ required: true });

const emit = defineEmits<{ change: [value: number] }>();

const { t } = useI18n();

const set = (value: number): void => {
    const next = Math.min(props.max, Math.max(props.min, value));
    if (next !== model.value) {
        model.value = next;
        emit('change', next);
    }
};
</script>

<template>
    <div class="inline-flex items-stretch border-[1.5px] border-mi-ligne bg-mi-blanc" :class="compact ? 'h-9' : 'h-11'">
        <button
            type="button"
            class="flex items-center justify-center text-mi-indigo transition-colors duration-150 hover:bg-mi-ecru disabled:cursor-not-allowed disabled:opacity-40"
            :class="compact ? 'w-8' : 'w-10'"
            :disabled="disabled || model <= min"
            :aria-label="t('cart.decrease')"
            @click="set(model - 1)"
        >
            <span aria-hidden="true" class="text-lg leading-none">−</span>
        </button>
        <output class="flex min-w-[2.25rem] items-center justify-center text-[14px] font-medium tabular-nums text-mi-charbon" :aria-label="t('cart.qty')">
            {{ model }}
        </output>
        <button
            type="button"
            class="flex items-center justify-center text-mi-indigo transition-colors duration-150 hover:bg-mi-ecru disabled:cursor-not-allowed disabled:opacity-40"
            :class="compact ? 'w-8' : 'w-10'"
            :disabled="disabled || model >= max"
            :aria-label="t('cart.increase')"
            @click="set(model + 1)"
        >
            <MiIcon name="close" :size="14" class="rotate-45" />
        </button>
    </div>
</template>
