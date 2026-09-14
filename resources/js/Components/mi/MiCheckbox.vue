<script setup lang="ts">
import MiIcon from '@/Components/mi/MiIcon.vue';
import { useId } from 'vue';

/**
 * Case à cocher de la maison : carré indigo, coche écru.
 */
withDefaults(
    defineProps<{
        label: string;
        name?: string;
        disabled?: boolean;
    }>(),
    { name: undefined, disabled: false },
);

const model = defineModel<boolean>({ required: true });

const id = useId();
</script>

<template>
    <label :for="id" class="inline-flex cursor-pointer items-center gap-3 text-[15px] text-mi-charbon">
        <span class="relative flex size-5 shrink-0 items-center justify-center">
            <input
                :id="id"
                v-model="model"
                type="checkbox"
                :name="name"
                :disabled="disabled"
                class="peer size-5 cursor-pointer appearance-none border-2 border-mi-indigo bg-mi-blanc transition-colors duration-150 checked:bg-mi-indigo disabled:cursor-not-allowed disabled:opacity-50"
            />
            <MiIcon
                name="check"
                :size="14"
                class="pointer-events-none absolute text-mi-ecru opacity-0 transition-opacity duration-150 peer-checked:opacity-100"
            />
        </span>
        <span>{{ label }}</span>
    </label>
</template>
