<script setup lang="ts">
import MiIcon from '@/Components/mi/MiIcon.vue';
import { computed, useId } from 'vue';

/**
 * Option en carte : radio natif masqué, bord indigo quand sélectionné.
 */
const props = withDefaults(
    defineProps<{
        value: string;
        label: string;
        description?: string;
        name: string;
        disabled?: boolean;
    }>(),
    { description: undefined, disabled: false },
);

const model = defineModel<string>({ required: true });

const id = useId();
const checked = computed(() => model.value === props.value);
</script>

<template>
    <label
        :for="id"
        class="flex cursor-pointer items-start gap-4 border-[1.5px] p-4 transition-colors duration-150"
        :class="checked ? 'border-mi-indigo bg-mi-blanc' : 'border-mi-ligne bg-mi-blanc hover:border-[#cfcac0]'"
    >
        <input :id="id" v-model="model" type="radio" :name="name" :value="value" :disabled="disabled" class="peer sr-only" />
        <span
            class="mt-0.5 flex size-5 shrink-0 items-center justify-center border-2 transition-colors duration-150"
            :class="checked ? 'border-mi-indigo bg-mi-indigo text-mi-ecru' : 'border-mi-indigo bg-mi-blanc text-transparent'"
            aria-hidden="true"
        >
            <MiIcon name="check" :size="13" />
        </span>
        <span class="flex min-w-0 flex-col gap-1">
            <span class="text-[15px] font-medium text-mi-charbon">{{ label }}</span>
            <span v-if="description" class="text-small leading-relaxed text-mi-fil">{{ description }}</span>
            <slot />
        </span>
    </label>
</template>
