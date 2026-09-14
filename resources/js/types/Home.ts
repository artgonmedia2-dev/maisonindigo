import type { ProductCard } from './Catalog';
import type { MetaProps } from './index';

export interface HomePageProps {
    meta: MetaProps;
    newProducts: ProductCard[];
}
