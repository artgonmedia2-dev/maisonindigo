<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import MiInput from '@/Components/mi/MiInput.vue';
import { useI18n } from '@/composables/useI18n';
import { useRoute } from '@/composables/useRoute';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const { t } = useI18n();
const route = useRoute();

const passwordInput = ref<InstanceType<typeof MiInput> | null>(null);
const currentPasswordInput = ref<InstanceType<typeof MiInput> | null>(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = (): void => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-h3">{{ t('profile.passwordTitle') }}</h2>
            <p class="mt-2 text-[15px] leading-relaxed text-mi-charbon/85">{{ t('profile.passwordLead') }}</p>
        </header>

        <form class="mt-7 flex flex-col gap-6" @submit.prevent="updatePassword">
            <MiInput
                ref="currentPasswordInput"
                v-model="form.current_password"
                type="password"
                name="current_password"
                :label="t('profile.currentPassword')"
                :error="form.errors.current_password"
                autocomplete="current-password"
                required
            />

            <MiInput
                ref="passwordInput"
                v-model="form.password"
                type="password"
                name="password"
                :label="t('profile.newPassword')"
                :error="form.errors.password"
                autocomplete="new-password"
                required
            />

            <MiInput
                v-model="form.password_confirmation"
                type="password"
                name="password_confirmation"
                :label="t('profile.passwordConfirm')"
                :error="form.errors.password_confirmation"
                autocomplete="new-password"
                required
            />

            <div class="flex items-center gap-5">
                <MiButton type="submit" variant="primary" :loading="form.processing">{{ t('profile.save') }}</MiButton>
                <Transition enter-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-active-class="transition-opacity duration-150" leave-to-class="opacity-0">
                    <p v-if="form.recentlySuccessful" class="text-small font-medium text-mi-vert" role="status">{{ t('profile.saved') }}</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
