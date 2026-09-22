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

export interface HomePageProps {
    meta: MetaProps;
    newProducts: ProductCard[];
    collections: HomeCollections;
}
