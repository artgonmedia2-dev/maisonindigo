import { inject } from 'vue';
import { route as ziggyRoute } from 'ziggy-js';

type RouteFn = typeof ziggyRoute;

/**
 * Fonction `route()` de Ziggy utilisable dans `<script setup>`, y compris en rendu serveur.
 * Le plugin ZiggyVue la fournit via `provide('route')` avec la configuration de la page.
 */
export function useRoute(): RouteFn {
    const injected = inject<RouteFn | null>('route', null);

    if (injected !== null) {
        return injected;
    }

    if (typeof globalThis.route === 'function') {
        return globalThis.route;
    }

    throw new Error('Ziggy : la fonction route() est indisponible. Installez le plugin ZiggyVue.');
}
