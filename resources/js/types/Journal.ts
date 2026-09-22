import type { ProductCard } from './Catalog';
import type { BreadcrumbItem, ContentBlock, FaqEntry } from './Seo';

export interface ArticleCard {
    slug: string;
    title: string;
    excerpt: string;
    published_at: string | null;
    author: string | null;
    cover: string | null;
}

export interface ArticleAuthor {
    name: string;
    role: string | null;
    url: string;
}

export interface ArticleDetail {
    slug: string;
    title: string;
    /** « En bref » : la réponse directe, en deux ou trois phrases. */
    excerpt: string;
    blocks: ContentBlock[];
    faq: FaqEntry[];
    published_at: string | null;
    updated_at: string | null;
    cover: string | null;
    author: ArticleAuthor | null;
}

export interface JournalIndexProps {
    breadcrumb: BreadcrumbItem[];
    articles: ArticleCard[];
}

export interface JournalShowProps {
    breadcrumb: BreadcrumbItem[];
    article: ArticleDetail;
    products: ProductCard[];
}

export interface JournalAuthorProps {
    breadcrumb: BreadcrumbItem[];
    author: {
        name: string;
        role: string | null;
        bio: string | null;
        portrait: string | null;
    };
    articles: ArticleCard[];
}
