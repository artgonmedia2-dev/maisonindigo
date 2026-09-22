<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Models\Cut;
use App\Models\SizeChart;
use App\Models\SizeChartRow;
use App\Support\CatalogTerms;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SizeChart>
 */
class SizeChartFactory extends Factory
{
    public function definition(): array
    {
        /** @var Gender $gender */
        $gender = fake()->randomElement(Gender::cases());
        /** @var Cut $cut */
        $cut = fake()->randomElement(app(CatalogTerms::class)->activeCuts($gender)->all());

        return [
            'gender' => $gender,
            'cut' => $cut->slug,
            'title' => "{$gender->getLabel()} · {$cut->name}",
        ];
    }

    /**
     * Ajoute une ligne par taille paire de 26 à 42.
     */
    public function withRows(): static
    {
        return $this->afterCreating(function (SizeChart $chart): void {
            foreach (range(26, 42, 2) as $index => $size) {
                SizeChartRow::factory()->for($chart)->create([
                    'size' => $size,
                    'position' => $index,
                ]);
            }
        });
    }
}
