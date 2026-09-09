import { Config } from 'ziggy-js';

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
    contact: {
        email: string;
        whatsapp: string;
        city: string;
    };
}

export interface FlashProps {
    success: string | null;
    error: string | null;
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
    cart: {
        count: number;
    };
    flash: FlashProps;
    ziggy: Config & { location: string };
};
