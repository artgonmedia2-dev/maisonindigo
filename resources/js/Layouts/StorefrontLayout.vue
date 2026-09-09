<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import MiIcon from '@/Components/mi/MiIcon.vue';
import MiLogo from '@/Components/mi/MiLogo.vue';
import MiTrustBar from '@/Components/mi/MiTrustBar.vue';
import { useI18n } from '@/composables/useI18n';
import { useRoute } from '@/composables/useRoute';
import type { TranslationKey } from '@/i18n';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const { t, tc } = useI18n();
const route = useRoute();
const page = usePage();

const maison = computed(() => page.props.maison);
const cartCount = computed(() => page.props.cart.count);
const isAuthenticated = computed(() => page.props.auth.user !== null);
const year = new Date().getFullYear();

interface NavItem {
    key: TranslationKey;
    href: string;
    routeName: string;
}

const primaryNav: NavItem[] = [
    { key: 'nav.women', href: route('collections.women'), routeName: 'collections.women' },
    { key: 'nav.men', href: route('collections.men'), routeName: 'collections.men' },
    { key: 'nav.new', href: route('collections.new'), routeName: 'collections.new' },
    { key: 'nav.atelier', href: route('collections.atelier'), routeName: 'collections.atelier' },
    { key: 'nav.sizeQuiz', href: route('size-quiz'), routeName: 'size-quiz' },
];

const houseLinks: ReadonlyArray<{ key: TranslationKey; href: string }> = [
    { key: 'footer.about', href: '/la-maison' },
    { key: 'footer.sizeGuide', href: '/guide-des-tailles' },
    { key: 'footer.care', href: '/entretien' },
    { key: 'footer.faq', href: '/faq' },
    { key: 'footer.contact', href: '/contact' },
];

const legalLinks: ReadonlyArray<{ key: TranslationKey; href: string }> = [
    { key: 'footer.terms', href: '/cgv' },
    { key: 'footer.returns', href: '/retours' },
    { key: 'footer.privacy', href: '/confidentialite' },
];

const isCurrent = (routeName: string): boolean => route().current(routeName) === true;

const menuOpen = ref(false);
const memberEmail = ref('');

watch(
    () => page.url,
    () => {
        menuOpen.value = false;
    },
);

const accountHref = computed(() => (isAuthenticated.value ? route('dashboard') : route('login')));
const whatsappHref = computed(() => `https://wa.me/${maison.value.contact.whatsapp.replace(/[^\d]/g, '')}`);
</script>

<template>
    <div class="flex min-h-dvh flex-col bg-mi-ecru text-mi-charbon">
        <a
            href="#contenu"
            class="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-50 focus:bg-mi-indigo focus:px-4 focus:py-2 focus:text-mi-ecru"
        >
            {{ t('a11y.skipToContent') }}
        </a>

        <!-- En-tête -->
        <header class="sticky top-0 z-40 border-b border-mi-ligne bg-mi-ecru/95 backdrop-blur-sm">
            <div class="mi-container flex h-[4.5rem] items-center justify-between gap-6 md:h-24">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="-ms-2 p-2 text-mi-indigo md:hidden"
                        :aria-expanded="menuOpen"
                        aria-controls="menu-mobile"
                        :aria-label="menuOpen ? t('a11y.closeMenu') : t('a11y.openMenu')"
                        @click="menuOpen = !menuOpen"
                    >
                        <MiIcon :name="menuOpen ? 'close' : 'menu'" :size="24" />
                    </button>

                    <Link :href="route('home')" class="flex items-center" :aria-label="t('a11y.logo')">
                        <MiLogo variant="auto" tone="indigo" :width="132" />
                    </Link>
                </div>

                <nav class="hidden md:block" :aria-label="t('a11y.mainNavigation')">
                    <ul class="flex items-center gap-7 text-[15px] font-medium">
                        <li v-for="item in primaryNav" :key="item.key">
                            <Link
                                :href="item.href"
                                class="border-b-2 py-1 transition-colors duration-150 hover:text-mi-indigo"
                                :class="isCurrent(item.routeName) ? 'border-mi-ocre text-mi-indigo' : 'border-transparent text-mi-charbon'"
                                :aria-current="isCurrent(item.routeName) ? 'page' : undefined"
                            >
                                {{ t(item.key) }}
                            </Link>
                        </li>
                    </ul>
                </nav>

                <div class="flex items-center gap-1 text-mi-indigo sm:gap-2">
                    <button type="button" class="p-2" :aria-label="t('nav.search')" :title="t('nav.search')">
                        <MiIcon name="search" />
                    </button>
                    <Link :href="accountHref" class="p-2" :aria-label="t('nav.account')" :title="t('nav.account')">
                        <MiIcon name="account" />
                    </Link>
                    <button
                        type="button"
                        class="relative p-2"
                        :aria-label="tc('nav.cartCount', 'nav.cartCountPlural', cartCount)"
                        :title="t('nav.cart')"
                    >
                        <MiIcon name="cart" />
                        <span
                            v-if="cartCount > 0"
                            class="absolute -end-0.5 -top-0.5 min-w-5 bg-mi-indigo px-1 text-center text-[11px] font-semibold leading-5 text-mi-ecru"
                            aria-hidden="true"
                        >
                            {{ cartCount }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Menu mobile -->
            <nav
                v-show="menuOpen"
                id="menu-mobile"
                class="border-t border-mi-ligne bg-mi-ecru md:hidden"
                :aria-label="t('a11y.mainNavigation')"
            >
                <ul class="mi-container flex flex-col py-2">
                    <li v-for="item in primaryNav" :key="item.key">
                        <Link
                            :href="item.href"
                            class="block border-s-2 py-3 ps-4 text-h3 font-display font-semibold text-mi-indigo"
                            :class="isCurrent(item.routeName) ? 'border-mi-ocre' : 'border-transparent'"
                            :aria-current="isCurrent(item.routeName) ? 'page' : undefined"
                        >
                            {{ t(item.key) }}
                        </Link>
                    </li>
                </ul>
            </nav>
        </header>

        <main id="contenu" class="flex-1">
            <slot />
        </main>

        <MiTrustBar />

        <!-- Pied de page -->
        <footer class="mi-twill bg-mi-indigo text-mi-ecru">
            <div class="mi-container grid grid-cols-1 gap-12 py-16 md:grid-cols-12 md:gap-8">
                <div class="md:col-span-4">
                    <MiLogo tone="ecru" :width="150" />
                    <p class="mt-5 font-display text-h3 italic text-mi-ecru">{{ maison.tagline }}</p>
                    <p class="mt-3 text-small text-mi-ciel">{{ t('brand.origin') }}</p>
                </div>

                <nav class="md:col-span-2" :aria-label="t('a11y.footerNavigation')">
                    <h2 class="mi-caps text-mi-ciel">{{ t('footer.shop') }}</h2>
                    <ul class="mt-4 space-y-2.5 text-[15px]">
                        <li v-for="item in primaryNav" :key="item.key">
                            <Link :href="item.href" class="hover:underline underline-offset-4">{{ t(item.key) }}</Link>
                        </li>
                    </ul>
                </nav>

                <div class="md:col-span-2">
                    <h2 class="mi-caps text-mi-ciel">{{ t('footer.house') }}</h2>
                    <ul class="mt-4 space-y-2.5 text-[15px]">
                        <li v-for="item in houseLinks" :key="item.key">
                            <a :href="item.href" class="hover:underline underline-offset-4">{{ t(item.key) }}</a>
                        </li>
                    </ul>
                </div>

                <div class="md:col-span-4">
                    <h2 class="mi-caps text-mi-ciel">{{ t('footer.members') }}</h2>
                    <p class="mt-4 max-w-sm text-[15px] text-mi-ecru/90">{{ t('footer.membersLead') }}</p>
                    <form class="mt-5 flex max-w-sm flex-col gap-3 sm:flex-row" @submit.prevent>
                        <label for="membre-email" class="sr-only">{{ t('footer.emailLabel') }}</label>
                        <input
                            id="membre-email"
                            v-model="memberEmail"
                            type="email"
                            name="email"
                            autocomplete="email"
                            :placeholder="t('footer.emailPlaceholder')"
                            class="min-w-0 flex-1 border-2 border-mi-ecru/40 bg-transparent px-4 py-3 text-[15px] text-mi-ecru placeholder:text-mi-ciel focus:border-mi-ecru focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-mi-ecru"
                        />
                        <MiButton
                            type="submit"
                            variant="outline"
                            class="border-mi-ecru text-mi-ecru hover:bg-mi-ecru hover:text-mi-indigo"
                            disabled
                        >
                            {{ t('footer.join') }}
                        </MiButton>
                    </form>
                    <p class="mt-3 text-small text-mi-ciel">{{ t('footer.membersSoon') }}</p>
                </div>
            </div>

            <div class="mi-container">
                <div class="mi-stitch opacity-90"></div>
            </div>

            <div class="mi-container flex flex-col gap-6 py-8 text-small text-mi-ciel md:flex-row md:items-center md:justify-between">
                <address class="flex flex-wrap gap-x-6 gap-y-2 not-italic">
                    <a :href="`mailto:${maison.contact.email}`" class="hover:text-mi-ecru">{{ maison.contact.email }}</a>
                    <a :href="whatsappHref" class="hover:text-mi-ecru" rel="noopener" target="_blank">
                        {{ t('footer.whatsapp') }} {{ maison.contact.whatsapp }}
                    </a>
                    <span>{{ maison.contact.city }}</span>
                </address>

                <nav :aria-label="t('a11y.legalNavigation')">
                    <ul class="flex flex-wrap gap-x-6 gap-y-2">
                        <li v-for="item in legalLinks" :key="item.key">
                            <a :href="item.href" class="hover:text-mi-ecru">{{ t(item.key) }}</a>
                        </li>
                    </ul>
                </nav>

                <p>{{ t('footer.copyright', { year }) }}</p>
            </div>
        </footer>
    </div>
</template>
