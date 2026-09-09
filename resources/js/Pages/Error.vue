<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import { useI18n } from '@/composables/useI18n';
import type { TranslationKey } from '@/i18n';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { ErrorPageProps, ErrorStatus } from '@/types/Error';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<ErrorPageProps>();

const { t } = useI18n();

const copy: Record<ErrorStatus, { title: TranslationKey; text: TranslationKey }> = {
    403: { title: 'error.forbiddenTitle', text: 'error.forbiddenText' },
    404: { title: 'error.notFoundTitle', text: 'error.notFoundText' },
    500: { title: 'error.serverErrorTitle', text: 'error.serverErrorText' },
    503: { title: 'error.unavailableTitle', text: 'error.unavailableText' },
};

const current = computed(() => copy[props.status] ?? copy[500]);
</script>

<template>
    <Head>
        <title>{{ t(current.title) }}</title>
    </Head>

    <StorefrontLayout>
        <section class="mi-container flex min-h-[60dvh] flex-col justify-center py-20 md:py-32">
            <p class="mi-caps text-mi-stone">{{ props.status }}</p>
            <h1 class="mt-5 max-w-2xl font-display text-h1 font-semibold text-mi-indigo md:text-[3.5rem] md:leading-[1.05]">
                {{ t(current.title) }}
            </h1>
            <p class="mt-6 max-w-lg text-[17px] leading-relaxed text-mi-charbon/85">
                {{ t(current.text) }}
            </p>
            <div class="mt-10">
                <MiButton variant="outline" :href="route('home')">{{ t('error.backHome') }}</MiButton>
            </div>
        </section>
    </StorefrontLayout>
</template>
