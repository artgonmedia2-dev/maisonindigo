<script setup lang="ts">
import MiIcon from '@/Components/mi/MiIcon.vue';
import { useI18n } from '@/composables/useI18n';
import { computed, onMounted, ref, useId } from 'vue';

/**
 * Champ de formulaire de la maison : libellé, champ, aide et erreur.
 * Un champ « password » propose d'afficher la saisie.
 */
type InputType = 'text' | 'email' | 'password' | 'tel' | 'search' | 'number';

const props = withDefaults(
    defineProps<{
        label: string;
        type?: InputType;
        name?: string;
        error?: string;
        hint?: string;
        placeholder?: string;
        autocomplete?: string;
        required?: boolean;
        disabled?: boolean;
        autofocus?: boolean;
        /** Masque visuellement le libellé (il reste lu par les lecteurs d'écran). */
        hideLabel?: boolean;
    }>(),
    {
        type: 'text',
        name: undefined,
        error: undefined,
        hint: undefined,
        placeholder: undefined,
        autocomplete: undefined,
        required: false,
        disabled: false,
        autofocus: false,
        hideLabel: false,
    },
);

const model = defineModel<string>({ required: true });

const { t } = useI18n();

const id = useId();
const hintId = `${id}-aide`;
const errorId = `${id}-erreur`;

const input = ref<HTMLInputElement | null>(null);
const revealed = ref(false);

const isPassword = computed(() => props.type === 'password');
const currentType = computed(() => (isPassword.value && revealed.value ? 'text' : props.type));
const hasError = computed(() => props.error !== undefined && props.error !== '');

const describedBy = computed(() => {
    const ids: string[] = [];
    if (props.hint) ids.push(hintId);
    if (hasError.value) ids.push(errorId);
    return ids.length > 0 ? ids.join(' ') : undefined;
});

onMounted(() => {
    if (props.autofocus) {
        input.value?.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <div class="flex flex-col gap-2">
        <div class="flex items-baseline justify-between gap-4">
            <label :for="id" class="text-small font-medium text-mi-charbon" :class="hideLabel ? 'sr-only' : ''">
                {{ label }}
            </label>
            <span v-if="!required && !hideLabel" class="text-small text-mi-fil">{{ t('form.optional') }}</span>
        </div>

        <div class="relative">
            <input
                :id="id"
                ref="input"
                v-model="model"
                :type="currentType"
                :name="name"
                :placeholder="placeholder"
                :autocomplete="autocomplete"
                :required="required"
                :disabled="disabled"
                :aria-invalid="hasError ? 'true' : undefined"
                :aria-describedby="describedBy"
                class="mi-input"
                :class="isPassword ? 'pe-12' : ''"
            />
            <button
                v-if="isPassword"
                type="button"
                class="absolute inset-y-0 end-0 flex w-12 items-center justify-center text-mi-fil transition-colors duration-150 hover:text-mi-indigo"
                :aria-label="revealed ? t('form.hidePassword') : t('form.showPassword')"
                :aria-pressed="revealed"
                @click="revealed = !revealed"
            >
                <MiIcon :name="revealed ? 'eye-off' : 'eye'" :size="20" />
            </button>
        </div>

        <p v-if="hint" :id="hintId" class="text-small text-mi-fil">{{ hint }}</p>
        <p v-if="hasError" :id="errorId" class="text-small text-mi-erreur" role="alert">{{ error }}</p>
    </div>
</template>
