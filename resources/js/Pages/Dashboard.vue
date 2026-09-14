<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import { useI18n } from '@/composables/useI18n';
import { useRoute } from '@/composables/useRoute';
import AccountLayout from '@/Layouts/AccountLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const { t } = useI18n();
const route = useRoute();
const page = usePage();

const user = computed(() => page.props.auth.user);
const firstName = computed(() => (user.value?.name ?? '').split(' ')[0] ?? '');
</script>

<template>
    <Head :title="t('account.title')" />

    <AccountLayout :title="t('account.hello', { name: firstName })" :lead="t('account.helloLead')">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <section class="mi-card p-7">
                <h2 class="text-h3">{{ t('account.ordersTitle') }}</h2>
                <p class="mt-3 text-[15px] leading-relaxed text-mi-charbon/85">{{ t('account.ordersEmpty') }}</p>
                <MiButton variant="ghost" :href="route('collections.new')" class="mt-4" arrow>{{ t('account.seeNew') }}</MiButton>
            </section>

            <section class="mi-card p-7">
                <h2 class="text-h3">{{ t('account.infoTitle') }}</h2>
                <dl class="mt-4 space-y-3 text-[15px]">
                    <div class="flex flex-col gap-0.5">
                        <dt class="text-small text-mi-fil">{{ t('auth.name') }}</dt>
                        <dd class="font-medium text-mi-charbon">{{ user?.name }}</dd>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <dt class="text-small text-mi-fil">{{ t('auth.email') }}</dt>
                        <dd class="font-medium text-mi-charbon">{{ user?.email }}</dd>
                    </div>
                </dl>
                <MiButton variant="ghost" :href="route('profile.edit')" class="mt-4" arrow>{{ t('account.edit') }}</MiButton>
            </section>
        </div>
    </AccountLayout>
</template>
