<script setup lang="ts">
import MiIcon, { type MiIconName } from '@/Components/mi/MiIcon.vue';
import MiLogo from '@/Components/mi/MiLogo.vue';
import { useI18n } from '@/composables/useI18n';
import { useRoute } from '@/composables/useRoute';
import type { TranslationKey } from '@/i18n';
import { Link } from '@inertiajs/vue3';

/**
 * Gabarit des pages d'authentification : panneau denim à gauche, formulaire à droite.
 * Sur mobile, seul le formulaire reste, sous le wordmark.
 */
withDefaults(
    defineProps<{
        title: string;
        lead?: string;
    }>(),
    { lead: undefined },
);

const { t } = useI18n();
const route = useRoute();

const trust: ReadonlyArray<{ icon: MiIconName; key: TranslationKey }> = [
    { icon: 'cash', key: 'trust.cod' },
    { icon: 'exchange', key: 'trust.exchange' },
    { icon: 'box', key: 'trust.delivery' },
];
</script>

<template>
    <div class="grid min-h-dvh bg-mi-ecru text-mi-charbon lg:grid-cols-[5fr_7fr]">
        <!-- Panneau denim -->
        <aside class="mi-twill hidden flex-col justify-between bg-mi-indigo p-10 text-mi-ecru lg:flex xl:p-14">
            <Link :href="route('home')" class="inline-flex" :aria-label="t('a11y.logo')">
                <MiLogo tone="ecru" :width="190" />
            </Link>

            <div class="relative max-w-md ps-8">
                <div class="absolute inset-y-0 start-0 border-s-2 border-dashed border-mi-ocre" aria-hidden="true"></div>
                <p class="mi-caps text-mi-ciel">{{ t('auth.asideKicker') }}</p>
                <p class="mt-4 font-display text-[2.5rem] font-semibold leading-[1.08] text-mi-ecru xl:text-[3rem]">
                    {{ t('auth.asideTitle') }}
                </p>
                <p class="mt-5 text-[15px] leading-relaxed text-mi-ciel">{{ t('auth.asideText') }}</p>
            </div>

            <ul class="flex flex-col gap-3 text-small text-mi-ciel">
                <li v-for="item in trust" :key="item.key" class="flex items-center gap-3">
                    <MiIcon :name="item.icon" :size="18" class="shrink-0 text-mi-ecru" />
                    <span>{{ t(item.key) }}</span>
                </li>
            </ul>
        </aside>

        <!-- Formulaire -->
        <div class="flex flex-col">
            <div class="flex items-center justify-between px-5 py-5 md:px-10 lg:justify-end">
                <Link :href="route('home')" class="lg:hidden" :aria-label="t('a11y.logo')">
                    <MiLogo tone="indigo" :width="120" />
                </Link>
                <Link :href="route('home')" class="mi-link inline-flex items-center gap-2 text-small font-medium text-mi-stone">
                    <MiIcon name="arrow-back" :size="16" /> {{ t('auth.backToShop') }}
                </Link>
            </div>

            <main class="flex flex-1 items-center px-5 py-10 md:px-10">
                <div class="mx-auto w-full max-w-md">
                    <h1 class="text-h1">{{ title }}</h1>
                    <p v-if="lead" class="mt-4 text-[15px] leading-relaxed text-mi-charbon/85">{{ lead }}</p>
                    <div class="mt-10">
                        <slot />
                    </div>
                </div>
            </main>

            <p class="px-5 py-6 text-small text-mi-fil md:px-10">{{ t('brand.origin') }}</p>
        </div>
    </div>
</template>
