<?php

namespace Database\Seeders;

use App\Enums\Cut;
use App\Enums\Gender;
use App\Models\SizeChart;
use App\Models\SizeChartRow;
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
            foreach (Cut::forGender($gender) as $cut) {
                /** @var SizeChart $chart */
                $chart = SizeChart::query()->updateOrCreate(
                    ['gender' => $gender, 'cut' => $cut],
                    ['title' => "{$gender->getLabel()} · {$cut->getLabel()}"],
                );

                $sizes = $gender === Gender::Homme ? range(28, 42) : range(26, 40);
                $position = 0;

                foreach ($sizes as $size) {
                    SizeChartRow::query()->updateOrCreate(
                        ['size_chart_id' => $chart->id, 'size' => $size],
                        [
                            ...$this->measurements($gender, $cut, $size),
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
    private function measurements(Gender $gender, Cut $cut, int $size): array
    {
        // Tour de taille du vêtement : taille US × 2,54 + aisance (2 cm homme, 1 cm femme, taille haute).
        $waist = round($size * 2.54 + ($gender === Gender::Homme ? 2.0 : 1.0), 1);

        $hipsGap = match ($cut) {
            Cut::Slim, Cut::Tapered => 21.0,
            Cut::Straight, Cut::Regular, Cut::Bootcut, Cut::Flare => 23.0,
            Cut::Relaxed, Cut::WideLeg, Cut::Mom => 25.0,
        };

        $thighRatio = match ($cut) {
            Cut::Slim => 0.34,
            Cut::Tapered, Cut::Straight, Cut::Bootcut, Cut::Flare => 0.36,
            Cut::Regular, Cut::Mom => 0.38,
            Cut::Relaxed, Cut::WideLeg => 0.40,
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
