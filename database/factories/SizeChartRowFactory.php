<?php

namespace Database\Factories;

use App\Models\SizeChart;
use App\Models\SizeChartRow;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SizeChartRow>
 */
class SizeChartRowFactory extends Factory
{
    public function definition(): array
    {
        $size = fake()->numberBetween(26, 42);
        // Approximation : tour de taille ≈ taille US × 2,54 + aisance.
        $waist = round($size * 2.54 + 2, 1);

        return [
            'size_chart_id' => SizeChart::factory(),
            'size' => $size,
            'waist_cm' => $waist,
            'hips_cm' => round($waist + 20, 1),
            'thigh_cm' => round($waist * 0.36 + 2, 1),
            'inseam_30' => 76.0,
            'inseam_32' => 81.0,
            'inseam_34' => 86.0,
            'position' => 0,
        ];
    }
}
