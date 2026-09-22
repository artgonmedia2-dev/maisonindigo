import { useRoute } from '@/composables/useRoute';
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

/** État du tiroir, partagé entre l'en-tête et les pages. */
const drawerOpen = ref(false);
const busy = ref(false);

/**
 * Panier : lecture du résumé partagé, ajout, mise à jour et retrait de lignes.
 * Les montants sont en centimes ; le serveur recalcule tout à chaque action.
 */
export function useCart() {
    const page = usePage();
    const route = useRoute();

    const cart = computed(() => page.props.cart);
    const isEmpty = computed(() => cart.value.count === 0);

    const openDrawer = (): void => {
        drawerOpen.value = true;
    };

    const closeDrawer = (): void => {
        drawerOpen.value = false;
    };

    /**
     * Ajoute une ou plusieurs lignes d'un coup.
     *
     * Le lot part en une seule requête : le serveur l'ajoute en transaction,
     * donc un panier ne se retrouve jamais avec la moitié d'un duo.
     */
    const addMany = (lines: Array<{ variant_id: number; qty: number }>): void => {
        busy.value = true;
        router.post(
            route('cart.store'),
            { items: lines },
            {
                preserveScroll: true,
                onSuccess: () => openDrawer(),
                onFinish: () => {
                    busy.value = false;
                },
            },
        );
    };

    const add = (variantId: number, qty = 1): void => {
        busy.value = true;
        router.post(
            route('cart.store'),
            { variant_id: variantId, qty },
            {
                preserveScroll: true,
                onSuccess: () => openDrawer(),
                onFinish: () => {
                    busy.value = false;
                },
            },
        );
    };

    const update = (itemId: number, qty: number): void => {
        busy.value = true;
        router.patch(
            route('cart.update', itemId),
            { qty },
            {
                preserveScroll: true,
                onFinish: () => {
                    busy.value = false;
                },
            },
        );
    };

    const remove = (itemId: number): void => {
        busy.value = true;
        router.delete(route('cart.destroy', itemId), {
            preserveScroll: true,
            onFinish: () => {
                busy.value = false;
            },
        });
    };

    return {
        cart,
        isEmpty,
        busy,
        drawerOpen,
        openDrawer,
        closeDrawer,
        add,
        addMany,
        update,
        remove,
    };
}
