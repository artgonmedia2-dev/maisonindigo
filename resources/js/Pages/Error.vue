<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import { useI18n } from '@/composables/useI18n';
import { useRoute } from '@/composables/useRoute';
import type { TranslationKey } from '@/i18n';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { ErrorPageProps, ErrorStatus } from '@/types/Error';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<ErrorPageProps>();

const { t } = useI18n();
const route = useRoute();
const page = usePage();

const copy: Record<ErrorStatus, { title: TranslationKey; text: TranslationKey }> = {
    403: { title: 'error.forbiddenTitle', text: 'error.forbiddenText' },
    404: { title: 'error.notFoundTitle', text: 'error.notFoundText' },
    500: { title: 'error.serverErrorTitle', text: 'error.serverErrorText' },
    503: { title: 'error.unavailableTitle', text: 'error.unavailableText' },
};

const current = computed(() => copy[props.status] ?? copy[500]);
const mailHref = computed(() => `mailto:${page.props.maison.contact.email}`);
</script>

<template>
    <Head>
        <title>{{ t(current.title) }}</title>
    </Head>

    <StorefrontLayout>
        <section class="mi-container grid min-h-[60dvh] grid-cols-1 items-center gap-10 py-20 md:grid-cols-12 md:py-28">
            <div class="md:col-span-7">
                <p class="font-display text-[5.5rem] font-medium leading-none text-mi-stone/35 md:text-[8rem]" aria-hidden="true">
                    {{ props.status }}
                </p>
                <h1 class="mt-2 max-w-2xl text-h1 md:text-[3.5rem] md:leading-[1.05]">
                    <span class="sr-only">{{ props.status }} · </span>{{ t(current.title) }}
                </h1>
                <p class="mt-6 max-w-lg text-[17px] leading-relaxed text-mi-charbon/85">{{ t(current.text) }}</p>
                <div class="mt-10 flex flex-wrap items-center gap-4">
                    <MiButton variant="outline" :href="route('home')">{{ t('error.backHome') }}</MiButton>
                    <MiButton variant="ghost" :href="mailHref" external>{{ t('error.writeUs') }}</MiButton>
                </div>
            </div>

            <div v-if="props.status === 404" class="md:col-span-4 md:col-start-9">
                <p class="mi-caps text-mi-stone">{{ t('error.browse') }}</p>
                <ul class="mt-4 border-t border-mi-ligne">
                    <li class="border-b border-mi-ligne">
                        <Link :href="route('collections.women')" class="mi-link block py-3 font-display text-h3 text-mi-indigo">{{ t('nav.women') }}</Link>
                    </li>
                    <li class="border-b border-mi-ligne">
                        <Link :href="route('collections.men')" class="mi-link block py-3 font-display text-h3 text-mi-indigo">{{ t('nav.men') }}</Link>
                    </li>
                    <li class="border-b border-mi-ligne">
                        <Link :href="route('collections.new')" class="mi-link block py-3 font-display text-h3 text-mi-indigo">{{ t('nav.new') }}</Link>
                    </li>
                </ul>
            </div>
        </section>
    </StorefrontLayout>
</template>
