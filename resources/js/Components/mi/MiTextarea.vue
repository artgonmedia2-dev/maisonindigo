<script setup lang="ts">
import { useI18n } from '@/composables/useI18n';
import { computed, useId } from 'vue';

/**
 * Zone de texte de la maison, avec aide et erreur.
 */
const props = withDefaults(
    defineProps<{
        label: string;
        name?: string;
        hint?: string;
        error?: string;
        rows?: number;
        required?: boolean;
        maxlength?: number;
    }>(),
    { name: undefined, hint: undefined, error: undefined, rows: 3, required: false, maxlength: undefined },
);

const model = defineModel<string>({ required: true });

const { t } = useI18n();

const id = useId();
const hintId = `${id}-aide`;
const errorId = `${id}-erreur`;
const hasError = computed(() => props.error !== undefined && props.error !== '');
</script>

<template>
    <div class="flex flex-col gap-2">
        <div class="flex items-baseline justify-between gap-4">
            <label :for="id" class="text-small font-medium text-mi-charbon">{{ label }}</label>
            <span v-if="!required" class="text-small text-mi-fil">{{ t('form.optional') }}</span>
        </div>
        <textarea
            :id="id"
            v-model="model"
            :name="name"
            :rows="rows"
            :required="required"
            :maxlength="maxlength"
            :aria-invalid="hasError ? 'true' : undefined"
            :aria-describedby="hasError ? errorId : hint ? hintId : undefined"
            class="mi-input resize-y"
        ></textarea>
        <p v-if="hint" :id="hintId" class="text-small text-mi-fil">{{ hint }}</p>
        <p v-if="hasError" :id="errorId" class="text-small text-mi-erreur" role="alert">{{ error }}</p>
    </div>
</template>
