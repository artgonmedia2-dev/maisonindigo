<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import MiInput from '@/Components/mi/MiInput.vue';
import MiModal from '@/Components/mi/MiModal.vue';
import { useI18n } from '@/composables/useI18n';
import { useRoute } from '@/composables/useRoute';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const { t } = useI18n();
const route = useRoute();

const confirming = ref(false);
const passwordInput = ref<InstanceType<typeof MiInput> | null>(null);

const form = useForm({
    password: '',
});

const openConfirmation = (): void => {
    confirming.value = true;
    void nextTick(() => passwordInput.value?.focus());
};

const closeConfirmation = (): void => {
    confirming.value = false;
    form.clearErrors();
    form.reset();
};

const deleteAccount = (): void => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeConfirmation(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-h3">{{ t('profile.deleteTitle') }}</h2>
            <p class="mt-2 text-[15px] leading-relaxed text-mi-charbon/85">{{ t('profile.deleteLead') }}</p>
        </header>

        <MiButton variant="outline" class="mt-7" @click="openConfirmation">{{ t('profile.delete') }}</MiButton>

        <MiModal :show="confirming" :title="t('profile.deleteConfirmTitle')" @close="closeConfirmation">
            <p class="text-[15px] leading-relaxed text-mi-charbon/85">{{ t('profile.deleteConfirmLead') }}</p>

            <form class="mt-6 flex flex-col gap-6" @submit.prevent="deleteAccount">
                <MiInput
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    name="password"
                    :label="t('auth.password')"
                    :error="form.errors.password"
                    autocomplete="current-password"
                    required
                />

                <div class="flex flex-wrap items-center justify-end gap-4">
                    <MiButton variant="ghost" @click="closeConfirmation">{{ t('profile.cancel') }}</MiButton>
                    <MiButton type="submit" variant="primary" :loading="form.processing">{{ t('profile.delete') }}</MiButton>
                </div>
            </form>
        </MiModal>
    </section>
</template>
