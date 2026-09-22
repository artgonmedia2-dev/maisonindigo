<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import MiCartDrawer from '@/Components/mi/MiCartDrawer.vue';
import MiIcon from '@/Components/mi/MiIcon.vue';
import MiLogo from '@/Components/mi/MiLogo.vue';
import MiNotice from '@/Components/mi/MiNotice.vue';
import MiTrustBar from '@/Components/mi/MiTrustBar.vue';
import { useCart } from '@/composables/useCart';
import { useI18n } from '@/composables/useI18n';
import { useRoute } from '@/composables/useRoute';
import type { TranslationKey } from '@/i18n';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const { t, tc } = useI18n();
const route = useRoute();
const page = usePage();
const { openDrawer } = useCart();

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

const accountHref = computed(() => (isAuthenticated.value ? route('dashboard') : route('login')));
const whatsappHref = computed(() => `https://wa.me/${maison.value.contact.whatsapp.replace(/[^\d]/g, '')}`);

/* Menu mobile : tiroir, verrou du défilement, fermeture par Échap et à la navigation */
const menuOpen = ref(false);
const memberEmail = ref('');

const closeMenu = (): void => {
    menuOpen.value = false;
};

const onKeydown = (event: KeyboardEvent): void => {
    if (event.key === 'Escape' && menuOpen.value) {
        closeMenu();
    }
};

watch(menuOpen, (open) => {
    if (typeof document === 'undefined') return;
    document.body.style.overflow = open ? 'hidden' : '';
});

watch(() => page.url, closeMenu);

onMounted(() => document.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => {
    document.removeEventListener('keydown', onKeydown);
    if (typeof document !== 'undefined') document.body.style.overflow = '';
});

/* Messages flash : affichés sous l'en-tête, refermables */
const dismissed = ref<string | null>(null);
const flashSuccess = computed(() => (page.props.flash.success !== dismissed.value ? page.props.flash.success : null));
const flashError = computed(() => (page.props.flash.error !== dismissed.value ? page.props.flash.error : null));
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
        <header class="sticky top-0 z-40 border-b border-mi-ligne bg-mi-ecru/95 backdrop-blur-sm max-w-full overflow-x-hidden">
            <div class="mi-container grid h-[4.25rem] grid-cols-[auto_1fr_auto] items-center gap-2 md:h-[5.5rem] md:gap-10">
                <!-- Début : menu mobile / logo desktop -->
                <div class="flex items-center min-w-0">
                    <button
                        type="button"
                        class="-ms-2.5 p-2.5 text-mi-indigo md:hidden"
                        :aria-expanded="menuOpen"
                        aria-controls="menu-mobile"
                        :aria-label="menuOpen ? t('a11y.closeMenu') : t('a11y.openMenu')"
                        @click="menuOpen = !menuOpen"
                    >
                        <MiIcon :name="menuOpen ? 'close' : 'menu'" :size="24" />
                    </button>

                    <Link :href="route('home')" class="hidden items-center md:flex shrink-0" :aria-label="t('a11y.logo')">
                        <MiLogo tone="indigo" :width="140" />
                    </Link>
                </div>

                <!-- Centre : logo mobile / navigation desktop -->
                <Link :href="route('home')" class="flex items-center justify-center min-w-0 overflow-hidden md:hidden" :aria-label="t('a11y.logo')">
                    <MiLogo variant="wordmark" tone="indigo" :width="104" class="max-w-full h-auto shrink" />
                </Link>

                <nav class="hidden justify-center md:flex" :aria-label="t('a11y.mainNavigation')">
                    <ul class="flex items-center gap-8 text-[15px] font-medium">
                        <li v-for="item in primaryNav" :key="item.key">
                            <Link
                                :href="item.href"
                                class="mi-nav-link text-mi-charbon transition-colors duration-150 hover:text-mi-indigo aria-[current=page]:text-mi-indigo"
                                :aria-current="isCurrent(item.routeName) ? 'page' : undefined"
                            >
                                {{ t(item.key) }}
                            </Link>
                        </li>
                    </ul>
                </nav>

                <!-- Fin : actions -->
                <div class="flex items-center justify-end gap-0.5 text-mi-indigo sm:gap-1.5">
                    <button
                        type="button"
                        class="hidden p-2.5 transition-colors duration-150 hover:text-mi-stone sm:block"
                        :aria-label="t('nav.search')"
                        :title="t('nav.search')"
                    >
                        <MiIcon name="search" />
                    </button>
                    <Link
                        :href="accountHref"
                        class="hidden p-2.5 transition-colors duration-150 hover:text-mi-stone sm:block"
                        :aria-label="t('nav.account')"
                        :title="t('nav.account')"
                    >
                        <MiIcon name="account" />
                    </Link>
                    <button
                        type="button"
                        class="relative -me-2.5 p-2.5 transition-colors duration-150 hover:text-mi-stone"
                        :aria-label="tc('nav.cartCount', 'nav.cartCountPlural', cartCount)"
                        :title="t('nav.cart')"
                        @click="openDrawer"
                    >
                        <MiIcon name="cart" />
                        <span
                            v-if="cartCount > 0"
                            class="absolute end-0.5 top-0.5 min-w-[1.125rem] bg-mi-indigo px-1 text-center text-[11px] font-semibold leading-[1.125rem] text-mi-ecru"
                            aria-hidden="true"
                        >
                            {{ cartCount }}
                        </span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Tiroir de navigation mobile -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            leave-active-class="transition-opacity duration-150"
            leave-to-class="opacity-0"
        >
            <div v-if="menuOpen" class="fixed inset-0 z-50 md:hidden">
                <div class="absolute inset-0 bg-mi-indigo-deep/60" aria-hidden="true" @click="closeMenu"></div>
                <nav
                    id="menu-mobile"
                    class="absolute inset-y-0 start-0 flex w-[86%] max-w-sm flex-col bg-mi-ecru"
                    :aria-label="t('a11y.mainNavigation')"
                >
                    <div class="flex h-[4.25rem] items-center justify-between border-b border-mi-ligne px-5">
                        <MiLogo variant="monogram" tone="indigo" :width="96" />
                        <button type="button" class="-me-2.5 p-2.5 text-mi-indigo" :aria-label="t('a11y.closeMenu')" @click="closeMenu">
                            <MiIcon name="close" :size="24" />
                        </button>
                    </div>

                    <ul class="flex flex-col px-5 py-6">
                        <li v-for="item in primaryNav" :key="item.key">
                            <Link
                                :href="item.href"
                                class="block border-s-2 py-3 ps-4 font-display text-[1.75rem] font-semibold leading-tight text-mi-indigo"
                                :class="isCurrent(item.routeName) ? 'border-mi-ocre' : 'border-transparent'"
                                :aria-current="isCurrent(item.routeName) ? 'page' : undefined"
                            >
                                {{ t(item.key) }}
                            </Link>
                        </li>
                    </ul>

                    <div class="mx-5 mi-stitch"></div>

                    <ul class="flex flex-col gap-1 px-5 py-6 text-[15px] font-medium">
                        <li>
                            <Link :href="accountHref" class="flex items-center gap-3 py-2 text-mi-charbon">
                                <MiIcon name="account" :size="20" class="text-mi-stone" /> {{ t('nav.account') }}
                            </Link>
                        </li>
                        <li>
                            <a href="/guide-des-tailles" class="flex items-center gap-3 py-2 text-mi-charbon">
                                <MiIcon name="ruler" :size="20" class="text-mi-stone" /> {{ t('footer.sizeGuide') }}
                            </a>
                        </li>
                        <li>
                            <a :href="whatsappHref" class="flex items-center gap-3 py-2 text-mi-charbon" rel="noopener" target="_blank">
                                <MiIcon name="chat" :size="20" class="text-mi-stone" /> {{ t('nav.help') }}
                            </a>
                        </li>
                    </ul>

                    <p class="mt-auto px-5 py-6 text-small text-mi-fil">{{ t('brand.origin') }}</p>
                </nav>
            </div>
        </Transition>

        <!-- Messages flash -->
        <div v-if="flashSuccess || flashError" class="mi-container pt-6">
            <MiNotice v-if="flashSuccess" kind="success" dismissible @dismiss="dismissed = flashSuccess">
                {{ flashSuccess }}
            </MiNotice>
            <MiNotice v-else-if="flashError" kind="error" dismissible @dismiss="dismissed = flashError">
                {{ flashError }}
            </MiNotice>
        </div>

        <main id="contenu" class="flex-1">
            <slot />
        </main>

        <MiTrustBar />

        <MiCartDrawer />

        <!-- Pied de page -->
        <footer class="mi-twill bg-mi-indigo text-mi-ecru">
            <div class="mi-container grid grid-cols-1 gap-12 py-16 md:grid-cols-12 md:gap-8 md:py-20">
                <div class="md:col-span-4">
                    <MiLogo tone="ecru" :width="168" />
                    <p class="mt-6 font-display text-[1.625rem] italic leading-tight text-mi-ecru">{{ maison.tagline }}</p>
                    <p class="mt-4 max-w-sm text-small leading-relaxed text-mi-ciel">{{ maison.entity }}.</p>
                    <p class="mt-4 text-small text-mi-ciel">{{ t('brand.origin') }}</p>
                </div>

                <nav class="md:col-span-2" :aria-label="t('a11y.footerNavigation')">
                    <h2 class="mi-caps text-mi-ciel">{{ t('footer.shop') }}</h2>
                    <ul class="mt-5 space-y-3 text-[15px]">
                        <li v-for="item in primaryNav" :key="item.key">
                            <Link :href="item.href" class="mi-link">{{ t(item.key) }}</Link>
                        </li>
                    </ul>
                </nav>

                <div class="md:col-span-2">
                    <h2 class="mi-caps text-mi-ciel">{{ t('footer.house') }}</h2>
                    <ul class="mt-5 space-y-3 text-[15px]">
                        <li v-for="item in houseLinks" :key="item.key">
                            <a :href="item.href" class="mi-link">{{ t(item.key) }}</a>
                        </li>
                    </ul>
                </div>

                <div class="md:col-span-4">
                    <h2 class="mi-caps text-mi-ciel">{{ t('footer.members') }}</h2>
                    <p class="mt-5 max-w-sm text-[15px] leading-relaxed text-mi-ecru/90">{{ t('footer.membersLead') }}</p>
                    <form class="mt-6 flex max-w-sm flex-col gap-3 sm:flex-row" @submit.prevent>
                        <label for="membre-email" class="sr-only">{{ t('footer.emailLabel') }}</label>
                        <input
                            id="membre-email"
                            v-model="memberEmail"
                            type="email"
                            name="email"
                            autocomplete="email"
                            :placeholder="t('footer.emailPlaceholder')"
                            class="min-w-0 flex-1 border-2 border-mi-ecru/40 bg-transparent px-4 py-3 text-[15px] text-mi-ecru transition-colors duration-150 placeholder:text-mi-ciel hover:border-mi-ecru/70 focus:border-mi-ecru focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-mi-ecru"
                        />
                        <MiButton type="submit" variant="outline" tone="ecru" disabled>
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
                <address class="flex flex-wrap gap-x-6 gap-y-3 not-italic">
                    <a :href="`mailto:${maison.contact.email}`" class="mi-link inline-flex items-center gap-2 hover:text-mi-ecru">
                        <MiIcon name="mail" :size="16" /> {{ maison.contact.email }}
                    </a>
                    <a :href="whatsappHref" class="mi-link inline-flex items-center gap-2 hover:text-mi-ecru" rel="noopener" target="_blank">
                        <MiIcon name="chat" :size="16" /> {{ maison.contact.whatsapp }}
                    </a>
                    <span class="inline-flex items-center gap-2">
                        <MiIcon name="pin" :size="16" /> {{ maison.contact.city }}
                    </span>
                </address>

                <nav :aria-label="t('a11y.legalNavigation')">
                    <ul class="flex flex-wrap gap-x-6 gap-y-2">
                        <li v-for="item in legalLinks" :key="item.key">
                            <a :href="item.href" class="mi-link hover:text-mi-ecru">{{ t(item.key) }}</a>
                        </li>
                    </ul>
                </nav>

                <p>{{ t('footer.copyright', { year }) }}</p>
            </div>
        </footer>
    </div>
</template>
