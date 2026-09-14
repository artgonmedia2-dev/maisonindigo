<script setup lang="ts">
import { useI18n } from '@/composables/useI18n';
import AccountLayout from '@/Layouts/AccountLayout.vue';
import { Head } from '@inertiajs/vue3';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const { t } = useI18n();
</script>

<template>
    <Head :title="t('profile.title')" />

    <AccountLayout :title="t('profile.title')" :lead="t('profile.lead')">
        <div class="flex flex-col gap-6">
            <section class="mi-card p-7 md:p-9">
                <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" class="max-w-xl" />
            </section>

            <section class="mi-card p-7 md:p-9">
                <UpdatePasswordForm class="max-w-xl" />
            </section>

            <section class="mi-card p-7 md:p-9">
                <DeleteUserForm class="max-w-xl" />
            </section>
        </div>
    </AccountLayout>
</template>
