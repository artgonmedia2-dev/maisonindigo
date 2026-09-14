<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import MiInput from '@/Components/mi/MiInput.vue';
import MiNotice from '@/Components/mi/MiNotice.vue';
import { useI18n } from '@/composables/useI18n';
import { useRoute } from '@/composables/useRoute';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const { t } = useI18n();
const route = useRoute();

const user = usePage().props.auth.user;

const form = useForm({
    name: user?.name ?? '',
    email: user?.email ?? '',
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-h3">{{ t('profile.infoTitle') }}</h2>
            <p class="mt-2 text-[15px] leading-relaxed text-mi-charbon/85">{{ t('profile.infoLead') }}</p>
        </header>

        <form class="mt-7 flex flex-col gap-6" @submit.prevent="form.patch(route('profile.update'))">
            <MiInput v-model="form.name" type="text" name="name" :label="t('auth.name')" :error="form.errors.name" autocomplete="name" required />

            <MiInput v-model="form.email" type="email" name="email" :label="t('auth.email')" :error="form.errors.email" autocomplete="username" required />

            <div v-if="mustVerifyEmail && user?.email_verified_at === null" class="flex flex-col gap-3">
                <p class="text-[15px] text-mi-charbon">
                    {{ t('profile.unverified') }}
                    <Link :href="route('verification.send')" method="post" as="button" class="mi-link ms-1 font-medium text-mi-stone">
                        {{ t('profile.resend') }}
                    </Link>
                </p>
                <MiNotice v-if="status === 'verification-link-sent'" kind="success">{{ t('profile.verificationSent') }}</MiNotice>
            </div>

            <div class="flex items-center gap-5">
                <MiButton type="submit" variant="primary" :loading="form.processing">{{ t('profile.save') }}</MiButton>
                <Transition enter-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-active-class="transition-opacity duration-150" leave-to-class="opacity-0">
                    <p v-if="form.recentlySuccessful" class="text-small font-medium text-mi-vert" role="status">{{ t('profile.saved') }}</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
