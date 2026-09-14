<script setup lang="ts">
import { useI18n } from '@/composables/useI18n';
import type { ProductVariant } from '@/types/Catalog';
import { computed } from 'vue';

/**
 * Sélecteur taille × longueur. Une taille en rupture reste visible, barrée,
 * en pointillé : on peut la choisir pour demander « Me prévenir ».
 */
const props = defineProps<{
    sizes: number[];
    lengths: number[];
    variants: ProductVariant[];
}>();

const size = defineModel<number | null>('size', { required: true });
const length = defineModel<number | null>('length', { required: true });

const { t } = useI18n();

const find = (s: number | null, l: number | null): ProductVariant | undefined =>
    s === null || l === null ? undefined : props.variants.find((v) => v.size === s && v.length === l);

/** Une taille est disponible si au moins une longueur l'est (ou la longueur choisie). */
const sizeAvailable = (s: number): boolean =>
    length.value === null
        ? props.variants.some((v) => v.size === s && v.in_stock)
        : (find(s, length.value)?.in_stock ?? false);

const lengthAvailable = (l: number): boolean =>
    size.value === null
        ? props.variants.some((v) => v.length === l && v.in_stock)
        : (find(size.value, l)?.in_stock ?? false);

const selected = computed(() => find(size.value, length.value));

const cellClass = (active: boolean, available: boolean): string => {
    if (active) {
        return available
            ? 'border-mi-indigo bg-mi-indigo text-mi-blanc'
            : 'border-mi-indigo border-dashed bg-mi-ecru text-mi-indigo line-through';
    }

    return available
        ? 'border-[#cfcac0] bg-mi-blanc text-mi-charbon hover:border-mi-indigo'
        : 'border-[#cfcac0] border-dashed bg-mi-blanc text-[#bdb8ae] line-through hover:border-mi-fil';
};
</script>

<template>
    <div class="flex flex-col gap-6">
        <div>
            <div class="flex items-baseline justify-between">
                <p id="taille-label" class="text-small font-medium text-mi-charbon">
                    {{ t('product.size') }}
                    <span v-if="size !== null" class="text-mi-fil">· {{ size }}</span>
                </p>
                <slot name="size-aside" />
            </div>
            <div class="mt-3 flex flex-wrap gap-2" role="radiogroup" aria-labelledby="taille-label">
                <button
                    v-for="s in sizes"
                    :key="s"
                    type="button"
                    role="radio"
                    :aria-checked="size === s"
                    :aria-label="sizeAvailable(s) ? String(s) : `${s}, ${t('product.outOfStock')}`"
                    class="flex h-11 w-[3.25rem] items-center justify-center border-[1.5px] text-[14px] font-medium transition-colors duration-150"
                    :class="cellClass(size === s, sizeAvailable(s))"
                    @click="size = s"
                >
                    {{ s }}
                </button>
            </div>
        </div>

        <div>
            <p id="longueur-label" class="text-small font-medium text-mi-charbon">
                {{ t('product.length') }}
                <span v-if="length !== null" class="text-mi-fil">· {{ length }}</span>
            </p>
            <div class="mt-3 flex flex-wrap gap-2" role="radiogroup" aria-labelledby="longueur-label">
                <button
                    v-for="l in lengths"
                    :key="l"
                    type="button"
                    role="radio"
                    :aria-checked="length === l"
                    :aria-label="lengthAvailable(l) ? String(l) : `${l}, ${t('product.outOfStock')}`"
                    class="flex h-11 w-[3.25rem] items-center justify-center border-[1.5px] text-[14px] font-medium transition-colors duration-150"
                    :class="cellClass(length === l, lengthAvailable(l))"
                    @click="length = l"
                >
                    {{ l }}
                </button>
            </div>
        </div>

        <p class="min-h-[1.125rem] text-small" aria-live="polite">
            <template v-if="selected === undefined">
                <span class="text-mi-fil">{{ size === null ? t('product.chooseSize') : t('product.chooseLength') }}</span>
            </template>
            <template v-else-if="!selected.in_stock">
                <span class="text-mi-charbon">{{ t('product.outOfStock') }}</span>
            </template>
            <template v-else-if="selected.stock === 1">
                <span class="font-medium text-mi-ocre">{{ t('product.lastOne') }}</span>
            </template>
            <template v-else-if="selected.low_stock">
                <span class="font-medium text-mi-ocre">{{ t('product.lowStock', { count: selected.stock }) }}</span>
            </template>
            <template v-else>
                <span class="font-medium text-mi-vert">{{ t('product.inStock') }}</span>
            </template>
        </p>
    </div>
</template>
