<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import MiInput from '@/Components/mi/MiInput.vue';
import MiNotice from '@/Components/mi/MiNotice.vue';
import { useI18n } from '@/composables/useI18n';
import { useRoute } from '@/composables/useRoute';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();

const { t } = useI18n();
const route = useRoute();

const form = useForm({
    email: '',
});

const submit = (): void => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head :title="t('auth.forgot')" />

    <GuestLayout :title="t('auth.forgotTitle')" :lead="t('auth.forgotLead')">
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

            <MiButton type="submit" variant="primary" block :loading="form.processing">{{ t('auth.sendLink') }}</MiButton>
        </form>

        <p class="mt-8 text-[15px]">
            <Link :href="route('login')" class="mi-link font-medium text-mi-stone">{{ t('auth.backToLogin') }}</Link>
        </p>
    </GuestLayout>
</template>
