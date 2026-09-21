import type { MetaProps } from './index';

export interface ShippingZoneOption {
    id: number;
    name: string;
    cities: string[];
    delay: string;
    price: number;
    free_threshold: number | null;
}

export type PaymentMethodValue = 'cod' | 'transfer';

export interface PaymentMethodOption {
    value: PaymentMethodValue;
    label: string;
    description: string;
}

/** Les quatre seuls champs du formulaire de commande. */
export interface CheckoutPrefill {
    name: string;
    phone: string;
    city: string;
    line1: string;
}

export interface CheckoutPageProps {
    meta: MetaProps;
    zones: ShippingZoneOption[];
    cities: string[];
    payment_methods: PaymentMethodOption[];
    prefill: CheckoutPrefill;
}

export interface OrderAddress {
    name: string;
    phone: string;
    city: string;
    line1: string;
    zone: string;
}

export interface OrderLine {
    id: number;
    title: string;
    size: number;
    length: number;
    sku: string;
    qty: number;
    unit_price: number;
    total: number;
}

export interface OrderSummary {
    number: string;
    status: string;
    status_label: string;
    payment_method: PaymentMethodValue;
    payment_label: string;
    subtotal: number;
    discount_total: number;
    shipping_total: number;
    total: number;
    address: OrderAddress;
    created_at: string | null;
    items: OrderLine[];
}

export interface BankDetails {
    holder: string | null;
    iban: string | null;
    bank: string | null;
}

export interface ConfirmationPageProps {
    meta: MetaProps;
    order: OrderSummary;
    bank: BankDetails;
}
