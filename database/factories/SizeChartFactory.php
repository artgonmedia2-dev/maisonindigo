<?php

namespace Database\Factories;

use App\Enums\Cut;
use App\Enums\Gender;
use App\Models\SizeChart;
use App\Models\SizeChartRow;
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
        $cut = fake()->randomElement(Cut::forGender($gender));

        return [
            'gender' => $gender,
            'cut' => $cut,
            'title' => "{$gender->getLabel()} · {$cut->getLabel()}",
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
