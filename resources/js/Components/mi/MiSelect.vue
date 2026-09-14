<script setup lang="ts">
import MiIcon from '@/Components/mi/MiIcon.vue';
import type { FilterOption } from '@/types/Catalog';
import { useId } from 'vue';

/**
 * Liste déroulante native, habillée comme un champ de la maison.
 */
withDefaults(
    defineProps<{
        label: string;
        options: FilterOption[];
        hideLabel?: boolean;
        name?: string;
    }>(),
    { hideLabel: false, name: undefined },
);

const model = defineModel<string>({ required: true });

const id = useId();
</script>

<template>
    <div class="flex flex-col gap-2">
        <label :for="id" class="text-small font-medium text-mi-charbon" :class="hideLabel ? 'sr-only' : ''">{{ label }}</label>
        <div class="relative">
            <select :id="id" v-model="model" :name="name" class="mi-input appearance-none pe-10">
                <option v-for="option in options" :key="option.value" :value="option.value">{{ option.label }}</option>
            </select>
            <MiIcon name="chevron" :size="18" class="pointer-events-none absolute end-3 top-1/2 -translate-y-1/2 text-mi-fil" />
        </div>
    </div>
</template>
