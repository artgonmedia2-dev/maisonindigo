import type { FilterOption, ProductCard } from './Catalog';
import type { MetaProps } from './index';

export type QuizGender = 'homme' | 'femme';
export type QuizHips = 'etroites' | 'moyennes' | 'larges';
export type QuizFit = 'ajuste' | 'droit' | 'ample';

export interface QuizAnswers {
    gender: QuizGender;
    waist_cm: number;
    height_cm: number;
    hips: QuizHips;
    fit: QuizFit;
}

export interface SizeRecommendation {
    gender: QuizGender;
    gender_label: string;
    cut: string;
    cut_label: string;
    size: number;
    length: number;
    label: string;
    alternative_cut: string;
    alternative_cut_label: string;
    advice: string;
}

export interface FitOption extends FilterOption {
    description: string;
}

export interface SizeQuizPageProps {
    meta: MetaProps;
    answers: QuizAnswers | null;
    result: SizeRecommendation | null;
    products: ProductCard[];
    options: {
        genders: FilterOption[];
        hips: FilterOption[];
        fits: FitOption[];
    };
}
