import { Config } from 'ziggy-js';
import type { CartSummary } from './Cart';

/** Client connecté (props partagées `auth.user`). Nul pour un visiteur. */
export interface AuthUser {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
}

/** Alias conservé pour les pages Breeze. */
export type User = AuthUser;

export interface MaisonProps {
    name: string;
    tagline: string;
    /** « Maison Indigo, marque marocaine de jeans premium basée à Nador » */
    entity: string;
    contact: {
        email: string;
        whatsapp: string;
        city: string;
    };
    /** Durée de l'échange offert, réglée dans le back-office. */
    exchange_days: number;
    /** Bandeau d'annonce, nul quand il est désactivé. */
    announcement: string | null;
}

export interface FlashProps {
    success: string | null;
    error: string | null;
    /** Identifiant de la variante qui vient d'être ajoutée au panier. */
    cart_added: number | null;
}

/** Métadonnées SEO d'une page. `title` nul = titre par défaut de la maison. */
export interface MetaProps {
    title: string | null;
    description: string | null;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: AuthUser | null;
    };
    maison: MaisonProps;
    cart: CartSummary;
    flash: FlashProps;
    ziggy: Config & { location: string };
};
