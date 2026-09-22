import type { Pagination, ProductCard } from './Catalog';
import type { BreadcrumbItem, ContentBlock, FaqEntry, HubLinks } from './Seo';

/** Ce que le serveur compose pour le title, la canonical et les schemas. */
export interface SeoProps {
    title: string;
    description: string;
    canonical: string;
    robots: string;
}

export interface HubFilters {
    cut: string[];
    wash: string[];
    size: number | null;
    length: number | null;
    sort: string;
}

export interface HubOptions {
    cuts: Array<{ value: string; label: string }>;
    washes: Array<{ value: string; label: string }>;
    sizes: number[];
    lengths: number[];
    sorts: Array<{ value: string; label: string }>;
}

export interface HubPageProps {
    seo: SeoProps;
    breadcrumb: BreadcrumbItem[];
    heading: string;
    intro: string | null;
    blocks: ContentBlock[];
    faq: FaqEntry[];
    filters: HubFilters;
    /** Le lavage porté par l'adresse, quand la page est une sous-collection. */
    washPath: string | null;
    products: ProductCard[];
    pagination: Pagination;
    options: HubOptions;
    links: HubLinks;
}
