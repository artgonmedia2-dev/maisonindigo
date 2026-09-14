<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import MiInput from '@/Components/mi/MiInput.vue';
import { useI18n } from '@/composables/useI18n';
import { useRoute } from '@/composables/useRoute';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const { t } = useI18n();
const route = useRoute();

const form = useForm({
    password: '',
});

const submit = (): void => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head :title="t('auth.confirmTitle')" />

    <GuestLayout :title="t('auth.confirmTitle')" :lead="t('auth.confirmLead')">
        <form class="flex flex-col gap-6" @submit.prevent="submit">
            <MiInput
                v-model="form.password"
                type="password"
                name="password"
                :label="t('auth.password')"
                :error="form.errors.password"
                autocomplete="current-password"
                required
                autofocus
            />

            <MiButton type="submit" variant="primary" block :loading="form.processing">{{ t('auth.confirm') }}</MiButton>
        </form>
    </GuestLayout>
</template>
