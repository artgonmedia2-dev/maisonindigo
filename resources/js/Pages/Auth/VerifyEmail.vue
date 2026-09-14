<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import MiNotice from '@/Components/mi/MiNotice.vue';
import { useI18n } from '@/composables/useI18n';
import { useRoute } from '@/composables/useRoute';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    status?: string;
}>();

const { t } = useI18n();
const route = useRoute();

const form = useForm({});

const submit = (): void => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <Head :title="t('auth.verifyTitle')" />

    <GuestLayout :title="t('auth.verifyTitle')" :lead="t('auth.verifyLead')">
        <MiNotice v-if="verificationLinkSent" kind="success" class="mb-6">{{ t('auth.verifySent') }}</MiNotice>

        <form class="flex flex-col gap-6" @submit.prevent="submit">
            <MiButton type="submit" variant="primary" block :loading="form.processing">{{ t('auth.resend') }}</MiButton>
        </form>

        <p class="mt-8 text-[15px]">
            <Link :href="route('logout')" method="post" as="button" class="mi-link font-medium text-mi-stone">
                {{ t('auth.logout') }}
            </Link>
        </p>
    </GuestLayout>
</template>
