<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import MiInput from '@/Components/mi/MiInput.vue';
import { useI18n } from '@/composables/useI18n';
import { useRoute } from '@/composables/useRoute';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    email: string;
    token: string;
}>();

const { t } = useI18n();
const route = useRoute();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = (): void => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <Head :title="t('auth.resetTitle')" />

    <GuestLayout :title="t('auth.resetTitle')" :lead="t('auth.resetLead')">
        <form class="flex flex-col gap-6" @submit.prevent="submit">
            <MiInput
                v-model="form.email"
                type="email"
                name="email"
                :label="t('auth.email')"
                :error="form.errors.email"
                autocomplete="username"
                required
            />

            <MiInput
                v-model="form.password"
                type="password"
                name="password"
                :label="t('auth.newPassword')"
                :error="form.errors.password"
                autocomplete="new-password"
                required
                autofocus
            />

            <MiInput
                v-model="form.password_confirmation"
                type="password"
                name="password_confirmation"
                :label="t('auth.passwordConfirm')"
                :error="form.errors.password_confirmation"
                autocomplete="new-password"
                required
            />

            <MiButton type="submit" variant="primary" block :loading="form.processing">{{ t('auth.reset') }}</MiButton>
        </form>
    </GuestLayout>
</template>
