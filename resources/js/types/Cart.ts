import type { ProductImage } from './Catalog';
import type { MetaProps } from './index';

export interface CartLine {
    id: number;
    variant_id: number;
    product_id: number;
    slug: string;
    title: string;
    gender: string;
    size: number;
    length: number;
    sku: string;
    qty: number;
    unit_price: number;
    total: number;
    stock: number;
    image: ProductImage | null;
}

export interface CartDiscount {
    total: number;
    label: string | null;
    code: string | null;
    code_error: string | null;
}

/** Résumé partagé avec toutes les pages (`page.props.cart`). Montants en centimes. */
export interface CartSummary {
    count: number;
    subtotal: number;
    discount: CartDiscount;
    total: number;
    free_shipping_threshold: number | null;
    free_shipping_remaining: number | null;
    items: CartLine[];
}

export interface CartPageProps {
    meta: MetaProps;
}
