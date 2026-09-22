<?php

namespace App\Actions\SizeQuiz;

use App\Data\SizeRecommendation;
use App\Enums\Gender;
use App\Models\Cut;
use App\Models\ProductVariant;
use App\Support\CatalogTerms;

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
     * La matrice de la maison, exprimée en identifiants courts. Une coupe
     * retirée du back-office est remplacée par la première encore proposée
     * pour ce genre : le quiz ne renvoie jamais vers une coupe absente.
     *
     * @return array{0: string, 1: string}
     */
    private function cuts(Gender $gender, string $hips, string $fit): array
    {
        $souhaitees = $gender === Gender::Homme
            ? match ($fit) {
                'ajuste' => $hips === 'larges' ? ['tapered', 'slim'] : ['slim', 'tapered'],
                'ample' => ['relaxed', 'regular'],
                default => $hips === 'larges' ? ['regular', 'straight'] : ['straight', 'regular'],
            }
        : match ($fit) {
            'ajuste' => $hips === 'larges' ? ['bootcut', 'slim'] : ['slim', 'straight'],
            'ample' => $hips === 'etroites' ? ['flare', 'wide_leg'] : ['wide_leg', 'mom'],
            default => $hips === 'larges' ? ['mom', 'straight'] : ['straight', 'mom'],
        };

        $proposees = app(CatalogTerms::class)->activeCuts($gender)
            ->map(fn (Cut $cut): string => $cut->slug)
            ->all();

        if ($proposees === []) {
            return [$souhaitees[0], $souhaitees[1]];
        }

        $retenues = array_values(array_intersect($souhaitees, $proposees));
        $repli = array_values(array_diff($proposees, $retenues));

        return [
            $retenues[0] ?? $proposees[0],
            $retenues[1] ?? $repli[0] ?? $retenues[0] ?? $proposees[0],
        ];
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
