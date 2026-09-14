<script setup lang="ts">
import MiIcon from '@/Components/mi/MiIcon.vue';
import { useI18n } from '@/composables/useI18n';
import { onBeforeUnmount, ref, watch } from 'vue';

/**
 * Boîte de dialogue de la maison, sur l'élément natif <dialog>.
 * Fermeture par Échap, par le fond ou par la croix.
 */
const props = withDefaults(
    defineProps<{
        show: boolean;
        title: string;
        closeable?: boolean;
    }>(),
    { closeable: true },
);

const emit = defineEmits<{ close: [] }>();

const { t } = useI18n();

const dialog = ref<HTMLDialogElement | null>(null);

const close = (): void => {
    if (props.closeable) {
        emit('close');
    }
};

watch(
    () => props.show,
    (show) => {
        const element = dialog.value;
        if (element === null) return;

        if (show && !element.open) {
            element.showModal();
            document.body.style.overflow = 'hidden';
        } else if (!show && element.open) {
            element.close();
            document.body.style.overflow = '';
        }
    },
    { flush: 'post' },
);

const onCancel = (event: Event): void => {
    event.preventDefault();
    close();
};

const onBackdropClick = (event: MouseEvent): void => {
    if (event.target === dialog.value) {
        close();
    }
};

onBeforeUnmount(() => {
    document.body.style.overflow = '';
});
</script>

<template>
    <dialog
        ref="dialog"
        class="m-auto w-[calc(100%-2.5rem)] max-w-md bg-transparent p-0 text-mi-charbon"
        @cancel="onCancel"
        @click="onBackdropClick"
    >
        <div class="mi-card p-7 md:p-9">
            <div class="flex items-start justify-between gap-6">
                <h2 class="text-h2">{{ title }}</h2>
                <button
                    v-if="closeable"
                    type="button"
                    class="-me-2 -mt-2 p-2 text-mi-fil transition-colors duration-150 hover:text-mi-indigo"
                    :aria-label="t('a11y.close')"
                    @click="close"
                >
                    <MiIcon name="close" :size="20" />
                </button>
            </div>
            <div class="mt-4">
                <slot />
            </div>
        </div>
    </dialog>
</template>
