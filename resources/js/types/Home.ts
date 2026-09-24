import type { ProductCard } from './Catalog';
import type { MetaProps } from './index';

/** Une des deux portes d'entrée du catalogue, réglée depuis le back-office. */
export interface HomeCollectionCard {
    title: string;
    text: string;
    /** Nombre de modèles en ligne : un repère concret avant le clic. */
    count: number;
    /** Visuel choisi dans le back-office ; sans lui, le gabarit denim. */
    image: string | null;
    badge: 'new' | 'limited' | 'atelier' | null;
}

export interface HomeCollections {
    /** Vrai si la section passe avant les nouveautés. */
    first: boolean;
    kicker: string;
    title: string;
    women: HomeCollectionCard;
    men: HomeCollectionCard;
}

/** L'image d'ouverture : textes réglés, chiffres lus du catalogue. */
export interface HomeHero {
    kicker: string;
    title: string;
    lead: string;
    cta: { label: string; url: string };
    /** Prix du modèle le moins cher, en centimes. */
    from_price: number | null;
    product: ProductCard | null;
}

/** Le bandeau de logos sous l'ouverture. Vide, la section ne s'affiche pas. */
export interface HomeBrands {
    title: string;
    items: Array<{ name: string; file: string }>;
}

/** Un avis client publié. */
export interface HomeReview {
    author: string;
    city: string | null;
    rating: number;
    body: string;
    size: string | null;
    /** Vrai seulement si une commande livrée porte l'avis. */
    verified: boolean;
    product: { title: string; url: string } | null;
    date: string | null;
}

export interface HomeReviews {
    title: string;
    /** Moyenne sur tous les avis publiés, pas seulement ceux montrés. */
    average: number | null;
    count: number;
    items: HomeReview[];
}

export interface HomePageProps {
    meta: MetaProps;
    newProducts: ProductCard[];
    hero: HomeHero;
    brands: HomeBrands;
    reviews: HomeReviews;
    collections: HomeCollections;
}
