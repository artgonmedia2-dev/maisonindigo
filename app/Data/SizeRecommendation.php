<?php

namespace App\Data;

use App\Enums\Cut;
use App\Enums\Gender;

/**
 * Résultat du quiz « Trouver ma taille ».
 */
final readonly class SizeRecommendation
{
    public function __construct(
        public Gender $gender,
        public Cut $cut,
        public int $size,
        public int $length,
        /** Coupe de repli si la première ne convient pas. */
        public Cut $alternativeCut,
        /** Conseil en une phrase, dans la voix de la maison. */
        public string $advice,
    ) {}

    /**
     * @return array{gender: string, gender_label: string, cut: string, cut_label: string, size: int, length: int, label: string, alternative_cut: string, alternative_cut_label: string, advice: string}
     */
    public function toArray(): array
    {
        return [
            'gender' => $this->gender->value,
            'gender_label' => $this->gender->getLabel(),
            'cut' => $this->cut->value,
            'cut_label' => $this->cut->getLabel(),
            'size' => $this->size,
            'length' => $this->length,
            'label' => "{$this->size} / {$this->length}",
            'alternative_cut' => $this->alternativeCut->value,
            'alternative_cut_label' => $this->alternativeCut->getLabel(),
            'advice' => $this->advice,
        ];
    }
}
