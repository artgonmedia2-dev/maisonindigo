import { fr } from './fr';

type Leaves<T, Prefix extends string = ''> = T extends string
    ? Prefix
    : {
          [K in keyof T & string]: Leaves<
              T[K],
              Prefix extends '' ? K : `${Prefix}.${K}`
          >;
      }[keyof T & string];

/** Clé pointée vers un texte : « nav.women », « footer.copyright »… */
export type TranslationKey = Leaves<typeof fr>;

export type TranslationParams = Record<string, string | number>;

function lookup(key: string): string | undefined {
    let current: unknown = fr;

    for (const part of key.split('.')) {
        if (current === null || typeof current !== 'object') {
            return undefined;
        }

        current = (current as Record<string, unknown>)[part];
    }

    return typeof current === 'string' ? current : undefined;
}

/**
 * Retourne le texte de la clé, avec substitution des paramètres « :name ».
 */
export function t(key: TranslationKey, params?: TranslationParams): string {
    let text = lookup(key) ?? key;

    if (params) {
        for (const [name, value] of Object.entries(params)) {
            text = text.replaceAll(`:${name}`, String(value));
        }
    }

    return text;
}

/**
 * Pluriel simple : clé singulier / clé « Plural » selon le compte.
 */
export function tc(
    singular: TranslationKey,
    plural: TranslationKey,
    count: number,
): string {
    return t(count > 1 ? plural : singular, { count });
}

/**
 * Titre d'onglet : « Nouveautés · Maison Indigo », ou la signature seule.
 */
export function pageTitle(title: string): string {
    const trimmed = title.trim();

    return trimmed === ''
        ? `${fr.brand.name} · ${fr.brand.tagline}`
        : `${trimmed} · ${fr.brand.name}`;
}
