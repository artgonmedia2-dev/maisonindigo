<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import MiInput from '@/Components/mi/MiInput.vue';
import { useI18n } from '@/composables/useI18n';
import { useRoute } from '@/composables/useRoute';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const { t } = useI18n();
const route = useRoute();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = (): void => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <Head :title="t('auth.register')" />

    <GuestLayout :title="t('auth.registerTitle')" :lead="t('auth.registerLead')">
        <form class="flex flex-col gap-6" @submit.prevent="submit">
            <MiInput
                v-model="form.name"
                type="text"
                name="name"
                :label="t('auth.name')"
                :error="form.errors.name"
                autocomplete="name"
                required
                autofocus
            />

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
                :label="t('auth.password')"
                :error="form.errors.password"
                autocomplete="new-password"
                required
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

            <MiButton type="submit" variant="primary" block :loading="form.processing">{{ t('auth.register') }}</MiButton>
        </form>

        <p class="mt-8 text-[15px] text-mi-fil">
            {{ t('auth.haveAccount') }} ·
            <Link :href="route('login')" class="mi-link font-medium text-mi-indigo">{{ t('auth.login') }}</Link>
        </p>
    </GuestLayout>
</template>
