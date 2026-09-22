<?php

namespace Database\Seeders;

use App\Enums\Gender;
use App\Models\Cut;
use App\Models\SizeChart;
use App\Models\SizeChartRow;
use App\Support\CatalogTerms;
use Illuminate\Database\Seeder;

/**
 * Tableaux de mesures par genre × coupe, en centimètres.
 * Valeurs de départ cohérentes avec un sizing US ; à ajuster dans Filament
 * avec les mesures réelles des patrons.
 */
class SizeChartsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Gender::cases() as $gender) {
            foreach (app(CatalogTerms::class)->activeCuts($gender) as $cut) {
                /** @var Cut $cut */
                /** @var SizeChart $chart */
                $chart = SizeChart::query()->updateOrCreate(
                    ['gender' => $gender, 'cut' => $cut->slug],
                    ['title' => "{$gender->getLabel()} · {$cut->name}"],
                );

                $sizes = $gender === Gender::Homme ? range(28, 42) : range(26, 40);
                $position = 0;

                foreach ($sizes as $size) {
                    SizeChartRow::query()->updateOrCreate(
                        ['size_chart_id' => $chart->id, 'size' => $size],
                        [
                            ...$this->measurements($gender, $cut->slug, $size),
                            'position' => $position++,
                        ],
                    );
                }
            }
        }
    }

    /**
     * @return array{waist_cm: float, hips_cm: float, thigh_cm: float, inseam_30: float, inseam_32: float, inseam_34: float}
     */
    private function measurements(Gender $gender, string $cut, int $size): array
    {
        // Tour de taille du vêtement : taille US × 2,54 + aisance (2 cm homme, 1 cm femme, taille haute).
        $waist = round($size * 2.54 + ($gender === Gender::Homme ? 2.0 : 1.0), 1);

        // Une coupe ajoutée depuis le back-office prend l'aisance médiane.
        $hipsGap = match ($cut) {
            'slim', 'tapered' => 21.0,
            'relaxed', 'wide_leg' => 25.0,
            'mom' => 25.0,
            default => 23.0,
        };

        $thighRatio = match ($cut) {
            'slim' => 0.34,
            'regular', 'mom' => 0.38,
            'relaxed', 'wide_leg' => 0.40,
            default => 0.36,
        };

        return [
            'waist_cm' => $waist,
            'hips_cm' => round($waist + $hipsGap, 1),
            'thigh_cm' => round($waist * $thighRatio + 2, 1),
            'inseam_30' => 76.0,
            'inseam_32' => 81.0,
            'inseam_34' => 86.0,
        ];
    }
}
