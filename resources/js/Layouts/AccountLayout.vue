<script setup lang="ts">
import MiIcon, { type MiIconName } from '@/Components/mi/MiIcon.vue';
import { useI18n } from '@/composables/useI18n';
import { useRoute } from '@/composables/useRoute';
import type { TranslationKey } from '@/i18n';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * Espace client : navigation latérale et contenu, dans le gabarit de la boutique.
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
const page = usePage();

const userName = computed(() => page.props.auth.user?.name ?? '');

interface AccountLink {
    key: TranslationKey;
    icon: MiIconName;
    href: string | null;
    routeName: string | null;
}

const links: AccountLink[] = [
    { key: 'account.overview', icon: 'account', href: route('dashboard'), routeName: 'dashboard' },
    { key: 'account.profile', icon: 'ruler', href: route('profile.edit'), routeName: 'profile.edit' },
    { key: 'account.orders', icon: 'box', href: null, routeName: null },
    { key: 'account.addresses', icon: 'pin', href: null, routeName: null },
];

const isCurrent = (routeName: string | null): boolean => routeName !== null && route().current(routeName) === true;
</script>

<template>
    <StorefrontLayout>
        <section class="mi-container py-12 md:py-16">
            <p class="mi-caps text-mi-stone">{{ t('account.title') }}</p>

            <div class="mt-6 grid grid-cols-1 gap-10 md:grid-cols-12 md:gap-12">
                <aside class="md:col-span-3">
                    <p class="font-display text-h3 text-mi-indigo">{{ userName }}</p>
                    <nav class="mt-5" :aria-label="t('a11y.accountNavigation')">
                        <ul class="flex flex-col border-t border-mi-ligne">
                            <li v-for="link in links" :key="link.key" class="border-b border-mi-ligne">
                                <Link
                                    v-if="link.href"
                                    :href="link.href"
                                    class="flex items-center gap-3 border-s-2 py-3 ps-3 text-[15px] font-medium transition-colors duration-150 hover:text-mi-indigo"
                                    :class="isCurrent(link.routeName) ? 'border-mi-ocre text-mi-indigo' : 'border-transparent text-mi-charbon'"
                                    :aria-current="isCurrent(link.routeName) ? 'page' : undefined"
                                >
                                    <MiIcon :name="link.icon" :size="18" class="text-mi-stone" />
                                    {{ t(link.key) }}
                                </Link>
                                <span
                                    v-else
                                    class="flex items-center justify-between gap-3 border-s-2 border-transparent py-3 ps-3 text-[15px] text-mi-fil"
                                >
                                    <span class="flex items-center gap-3">
                                        <MiIcon :name="link.icon" :size="18" />
                                        {{ t(link.key) }}
                                    </span>
                                    <span class="mi-caps text-[11px]">{{ t('account.soon') }}</span>
                                </span>
                            </li>
                            <li>
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="flex w-full items-center gap-3 border-s-2 border-transparent py-3 ps-3 text-start text-[15px] font-medium text-mi-stone transition-colors duration-150 hover:text-mi-indigo"
                                >
                                    <MiIcon name="logout" :size="18" />
                                    {{ t('account.logout') }}
                                </Link>
                            </li>
                        </ul>
                    </nav>
                </aside>

                <div class="md:col-span-9">
                    <h1 class="text-h1">{{ title }}</h1>
                    <p v-if="lead" class="mt-3 max-w-xl text-[15px] leading-relaxed text-mi-charbon/85">{{ lead }}</p>
                    <div class="mt-8">
                        <slot />
                    </div>
                </div>
            </div>
        </section>
    </StorefrontLayout>
</template>
