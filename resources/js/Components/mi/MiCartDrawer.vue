<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import MiCartLine from '@/Components/mi/MiCartLine.vue';
import MiFreeShippingBar from '@/Components/mi/MiFreeShippingBar.vue';
import MiIcon from '@/Components/mi/MiIcon.vue';
import MiPrice from '@/Components/mi/MiPrice.vue';
import { useCart } from '@/composables/useCart';
import { useI18n } from '@/composables/useI18n';
import { useRoute } from '@/composables/useRoute';
import { Link, usePage } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, watch } from 'vue';

/**
 * Panier tiroir : s'ouvre à l'ajout d'un article et depuis l'icône de l'en-tête.
 */
const { t, tc } = useI18n();
const route = useRoute();
const page = usePage();
const { cart, isEmpty, drawerOpen, closeDrawer, openDrawer } = useCart();

const onKeydown = (event: KeyboardEvent): void => {
    if (event.key === 'Escape' && drawerOpen.value) {
        closeDrawer();
    }
};

watch(drawerOpen, (open) => {
    if (typeof document === 'undefined') return;
    document.body.style.overflow = open ? 'hidden' : '';
});

/* Ouverture après un ajout côté serveur (flash `cart_added`). */
watch(
    () => page.props.flash.cart_added,
    (added) => {
        if (added !== null && added !== undefined) {
            openDrawer();
        }
    },
);

/* Un tiroir vide se referme de lui-même après le dernier retrait. */
watch(isEmpty, (empty) => {
    if (empty && drawerOpen.value) {
        closeDrawer();
    }
});

onMounted(() => document.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => {
    document.removeEventListener('keydown', onKeydown);
    if (typeof document !== 'undefined') document.body.style.overflow = '';
});
</script>

<template>
    <Transition
        enter-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        leave-active-class="transition-opacity duration-150"
        leave-to-class="opacity-0"
    >
        <div v-if="drawerOpen" class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-mi-indigo-deep/60" aria-hidden="true" @click="closeDrawer"></div>

            <aside
                class="absolute inset-y-0 end-0 flex w-full max-w-md flex-col bg-mi-ecru"
                role="dialog"
                aria-modal="true"
                :aria-label="t('cart.drawerTitle')"
            >
                <header class="flex h-[4.25rem] items-center justify-between border-b border-mi-ligne px-5">
                    <h2 class="text-h3">
                        {{ t('cart.drawerTitle') }}
                        <span class="text-mi-fil">· {{ cart.count }}</span>
                    </h2>
                    <button type="button" class="-me-2.5 p-2.5 text-mi-indigo" :aria-label="t('a11y.close')" @click="closeDrawer">
                        <MiIcon name="close" :size="24" />
                    </button>
                </header>

                <div v-if="isEmpty" class="flex flex-1 flex-col items-start justify-center gap-4 px-5">
                    <p class="text-h3">{{ t('cart.empty') }}</p>
                    <p class="text-[15px] text-mi-fil">{{ t('cart.emptyHint') }}</p>
                    <MiButton variant="outline" :href="route('collections.new')" @click="closeDrawer">{{ t('cart.continue') }}</MiButton>
                </div>

                <template v-else>
                    <div class="flex-1 overflow-y-auto px-5">
                        <div class="py-4">
                            <MiFreeShippingBar :threshold="cart.free_shipping_threshold" :remaining="cart.free_shipping_remaining" />
                        </div>
                        <ul class="divide-y divide-mi-ligne border-t border-mi-ligne">
                            <li v-for="line in cart.items" :key="line.id">
                                <MiCartLine :line="line" compact />
                            </li>
                        </ul>
                    </div>

                    <footer class="border-t border-mi-ligne bg-mi-blanc px-5 py-5">
                        <dl class="flex flex-col gap-2 text-[15px]">
                            <div v-if="cart.discount.total > 0" class="flex items-center justify-between text-mi-vert">
                                <dt>{{ cart.discount.label ?? t('cart.discount') }}</dt>
                                <dd class="tabular-nums">− <MiPrice :amount="cart.discount.total" class="[&_span]:text-mi-vert" /></dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="font-medium">{{ t('cart.subtotal') }} <span class="text-small font-normal text-mi-fil">· {{ t('cart.totalHint') }}</span></dt>
                                <dd><MiPrice :amount="cart.total" display size="lg" /></dd>
                            </div>
                        </dl>
                        <div class="mt-5 flex flex-col gap-3">
                            <MiButton variant="primary" block :href="route('checkout.show')" arrow @click="closeDrawer">{{ t('cart.checkout') }}</MiButton>
                            <Link :href="route('cart.index')" class="mi-link self-center text-small font-medium text-mi-stone" @click="closeDrawer">
                                {{ t('cart.viewCart') }}
                            </Link>
                        </div>
                        <p class="sr-only">{{ tc('nav.cartCount', 'nav.cartCountPlural', cart.count) }}</p>
                    </footer>
                </template>
            </aside>
        </div>
    </Transition>
</template>
