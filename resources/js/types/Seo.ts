/** Un maillon du fil d'Ariane, tel que le serveur le compose. */
export interface BreadcrumbItem {
    name: string;
    url: string;
}

/** Une section H2 du contenu enrichi. */
export interface ContentBlock {
    title: string;
    body: string;
}

/** Une entrée de FAQ, reprise dans le schema FAQPage. */
export interface FaqEntry {
    question: string;
    answer: string;
}

/** Un lien de maillage sortant. */
export interface OutboundLink {
    label: string;
    url: string;
    excerpt?: string;
}

export interface HubLinks {
    washes: OutboundLink[];
    sisters: OutboundLink[];
    articles: OutboundLink[];
    quiz: string;
}
