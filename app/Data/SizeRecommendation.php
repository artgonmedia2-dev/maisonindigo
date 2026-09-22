<?php

namespace App\Data;

use App\Enums\Gender;
use App\Support\CatalogTerms;

/**
 * Résultat du quiz « Trouver ma taille ».
 */
final readonly class SizeRecommendation
{
    public function __construct(
        public Gender $gender,
        /** Identifiant court de la coupe recommandée. */
        public string $cut,
        public int $size,
        public int $length,
        /** Coupe de repli si la première ne convient pas. */
        public string $alternativeCut,
        /** Conseil en une phrase, dans la voix de la maison. */
        public string $advice,
    ) {}

    /**
     * @return array{gender: string, gender_label: string, cut: string, cut_label: string, size: int, length: int, label: string, alternative_cut: string, alternative_cut_label: string, advice: string}
     */
    public function toArray(): array
    {
        $termes = app(CatalogTerms::class);

        return [
            'gender' => $this->gender->value,
            'gender_label' => $this->gender->getLabel(),
            'cut' => $this->cut,
            'cut_label' => $termes->cutLabel($this->cut),
            'size' => $this->size,
            'length' => $this->length,
            'label' => "{$this->size} / {$this->length}",
            'alternative_cut' => $this->alternativeCut,
            'alternative_cut_label' => $termes->cutLabel($this->alternativeCut),
            'advice' => $this->advice,
        ];
    }
}
