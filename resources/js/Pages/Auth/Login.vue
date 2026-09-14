<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import MiCheckbox from '@/Components/mi/MiCheckbox.vue';
import MiInput from '@/Components/mi/MiInput.vue';
import MiNotice from '@/Components/mi/MiNotice.vue';
import { useI18n } from '@/composables/useI18n';
import { useRoute } from '@/composables/useRoute';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const { t } = useI18n();
const route = useRoute();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = (): void => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <Head :title="t('auth.login')" />

    <GuestLayout :title="t('auth.loginTitle')" :lead="t('auth.loginLead')">
        <MiNotice v-if="status" kind="success" class="mb-6">{{ status }}</MiNotice>

        <form class="flex flex-col gap-6" @submit.prevent="submit">
            <MiInput
                v-model="form.email"
                type="email"
                name="email"
                :label="t('auth.email')"
                :error="form.errors.email"
                autocomplete="username"
                required
                autofocus
            />

            <MiInput
                v-model="form.password"
                type="password"
                name="password"
                :label="t('auth.password')"
                :error="form.errors.password"
                autocomplete="current-password"
                required
            />

            <div class="flex flex-wrap items-center justify-between gap-4">
                <MiCheckbox v-model="form.remember" name="remember" :label="t('auth.remember')" />
                <Link v-if="canResetPassword" :href="route('password.request')" class="mi-link text-small font-medium text-mi-stone">
                    {{ t('auth.forgot') }}
                </Link>
            </div>

            <MiButton type="submit" variant="primary" block :loading="form.processing">{{ t('auth.login') }}</MiButton>
        </form>

        <p class="mt-8 text-[15px] text-mi-fil">
            {{ t('auth.noAccount') }} ·
            <Link :href="route('register')" class="mi-link font-medium text-mi-indigo">{{ t('auth.register') }}</Link>
        </p>
    </GuestLayout>
</template>
