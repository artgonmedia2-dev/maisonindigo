import { pageTitle, t, tc } from '@/i18n';

/**
 * Accès aux textes de l'interface depuis les composants.
 */
export function useI18n() {
    return { t, tc, pageTitle };
}
