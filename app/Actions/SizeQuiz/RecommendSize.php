<?php

namespace App\Actions\SizeQuiz;

use App\Data\SizeRecommendation;
use App\Enums\Cut;
use App\Enums\Gender;
use App\Models\ProductVariant;

/**
 * Quiz en quatre réponses : genre, tour de taille et hauteur, hanches, tombé souhaité.
 * Déduit une taille (26–42), une longueur (30/32/34) et une coupe, plus une coupe de repli.
 */
class RecommendSize
{
    /** @var list<string> */
    public const HIPS = ['etroites', 'moyennes', 'larges'];

    /** @var list<string> */
    public const FITS = ['ajuste', 'droit', 'ample'];

    public function handle(Gender $gender, int $waistCm, int $heightCm, string $hips, string $fit): SizeRecommendation
    {
        $size = $this->size($gender, $waistCm, $hips, $fit);
        $length = $this->length($heightCm);
        [$cut, $alternative] = $this->cuts($gender, $hips, $fit);

        return new SizeRecommendation(
            gender: $gender,
            cut: $cut,
            size: $size,
            length: $length,
            alternativeCut: $alternative,
            advice: $this->advice($fit, $hips),
        );
    }

    /**
     * Taille US ≈ tour de taille en pouces. Les hanches larges gagnent une taille sur les coupes ajustées.
     */
    private function size(Gender $gender, int $waistCm, string $hips, string $fit): int
    {
        $size = (int) round($waistCm / 2.54);

        if ($hips === 'larges' && $fit === 'ajuste') {
            $size++;
        }

        if ($hips === 'etroites' && $fit === 'ample') {
            $size--;
        }

        [$min, $max] = $gender === Gender::Homme ? [28, 42] : [26, 40];

        return max($min, min($max, $size, max(ProductVariant::SIZES)));
    }

    private function length(int $heightCm): int
    {
        return match (true) {
            $heightCm < 168 => 30,
            $heightCm <= 180 => 32,
            default => 34,
        };
    }

    /**
     * @return array{0: Cut, 1: Cut}
     */
    private function cuts(Gender $gender, string $hips, string $fit): array
    {
        if ($gender === Gender::Homme) {
            return match ($fit) {
                'ajuste' => $hips === 'larges' ? [Cut::Tapered, Cut::Slim] : [Cut::Slim, Cut::Tapered],
                'ample' => [Cut::Relaxed, Cut::Regular],
                default => $hips === 'larges' ? [Cut::Regular, Cut::Straight] : [Cut::Straight, Cut::Regular],
            };
        }

        return match ($fit) {
            'ajuste' => $hips === 'larges' ? [Cut::Bootcut, Cut::Slim] : [Cut::Slim, Cut::Straight],
            'ample' => $hips === 'etroites' ? [Cut::Flare, Cut::WideLeg] : [Cut::WideLeg, Cut::Mom],
            default => $hips === 'larges' ? [Cut::Mom, Cut::Straight] : [Cut::Straight, Cut::Mom],
        };
    }

    private function advice(string $fit, string $hips): string
    {
        return match (true) {
            $fit === 'ajuste' => __('storefront.size_quiz.advice_fitted'),
            $hips === 'larges' => __('storefront.size_quiz.advice_hips'),
            default => __('storefront.size_quiz.advice_default'),
        };
    }
}
