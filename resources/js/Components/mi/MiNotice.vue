<script setup lang="ts">
import MiIcon, { type MiIconName } from '@/Components/mi/MiIcon.vue';
import { useI18n } from '@/composables/useI18n';

/**
 * Message d'état : confirmation, erreur ou information.
 * Fond blanc, bord fin, filet coloré côté début.
 */
type Kind = 'success' | 'error' | 'info';

withDefaults(
    defineProps<{
        kind?: Kind;
        dismissible?: boolean;
    }>(),
    { kind: 'info', dismissible: false },
);

const emit = defineEmits<{ dismiss: [] }>();

const { t } = useI18n();

const kindClass: Record<Kind, string> = {
    success: 'border-s-mi-vert text-mi-vert',
    error: 'border-s-mi-erreur text-mi-erreur',
    info: 'border-s-mi-stone text-mi-stone',
};

const kindIcon: Record<Kind, MiIconName> = {
    success: 'check',
    error: 'alert',
    info: 'info',
};
</script>

<template>
    <div
        :role="kind === 'error' ? 'alert' : 'status'"
        class="mi-card flex items-start gap-3 border-s-[3px] px-4 py-3.5 text-[15px]"
        :class="kindClass[kind]"
    >
        <MiIcon :name="kindIcon[kind]" :size="20" class="mt-0.5 shrink-0" />
        <div class="flex-1 text-mi-charbon">
            <slot />
        </div>
        <button
            v-if="dismissible"
            type="button"
            class="-me-1 -mt-0.5 p-1 text-mi-fil transition-colors duration-150 hover:text-mi-indigo"
            :aria-label="t('a11y.close')"
            @click="emit('dismiss')"
        >
            <MiIcon name="close" :size="18" />
        </button>
    </div>
</template>
