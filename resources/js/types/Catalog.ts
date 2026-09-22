import type { BreadcrumbItem, OutboundLink } from './Seo';
import type { MetaProps } from './index';

export type PatchKind = 'new' | 'limited' | 'atelier';

export interface ProductImage {
    src: string;
    srcset: string;
    avif_srcset: string;
    alt: string;
    view: string;
}

/** Carte produit des grilles (ProductCardResource). */
export interface ProductCard {
    id: number;
    slug: string;
    title: string;
    gender: 'homme' | 'femme';
    gender_label: string;
    cut: string;
    cut_label: string;
    wash: string;
    wash_label: string;
    subtitle: string;
    price: number;
    compare_at_price: number | null;
    patch: PatchKind | null;
    in_stock: boolean;
    image: ProductImage | null;
    url: string;
}

export interface ProductVariant {
    id: number;
    size: number;
    length: number;
    sku: string;
    stock: number;
    in_stock: boolean;
    low_stock: boolean;
}

export interface SizeChartRow {
    size: number;
    waist_cm: number;
    hips_cm: number;
    thigh_cm: number;
    inseam_30: number;
    inseam_32: number;
    inseam_34: number;
}

/** Fiche produit (ProductDetailResource). */
export interface ProductDetail extends ProductCard {
    description: string | null;
    fabric_origin: string | null;
    weight_oz: number | null;
    composition: string | null;
    model_height_cm: number | null;
    model_size: string | null;
    size_advice: string | null;
    sizes: number[];
    lengths: number[];
    variants: ProductVariant[];
    /** « Jean baggy homme · denim japonais 13 oz · tailles 28–42 » */
    subtitle: string;
    size_chart: SizeChartRow[];
    images: ProductImage[];
    meta: MetaProps;
}

export interface FilterOption {
    value: string;
    label: string;
}

export interface CollectionFilters {
    cut: string[];
    wash: string[];
    size: number | null;
    sort: string;
}

export interface Pagination {
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
    prev_url: string | null;
    next_url: string | null;
}

export type CollectionHandle = 'women' | 'men' | 'new' | 'atelier';

export interface CollectionPageProps {
    handle: CollectionHandle;
    meta: MetaProps;
    heading: string;
    lead: string;
    filters: CollectionFilters;
    products: ProductCard[];
    pagination: Pagination;
    options: {
        cuts: FilterOption[];
        washes: FilterOption[];
        sizes: number[];
        sorts: FilterOption[];
    };
}

/** Un autre lavage de la même coupe. */
export interface ProductSibling {
    slug: string;
    url: string;
    label: string;
    image: string | null;
    current: boolean;
}

/** L'offre en lot, telle que le panier l'appliquera. Montants en centimes. */
export interface PackOffer {
    quantity: number;
    subtotal: number;
    total: number;
    unit: number;
    label: string;
}

export interface ProductPageProps {
    product: ProductDetail;
    related: ProductCard[];
    meta: MetaProps;
    breadcrumb: BreadcrumbItem[];
    /** Le hub de la coupe : chaque fiche y renvoie. */
    hub: OutboundLink | null;
    siblings: ProductSibling[];
    pack: PackOffer | null;
}
