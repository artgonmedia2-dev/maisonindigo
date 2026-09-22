<?php

namespace Database\Factories;

use App\Models\Collection;
use App\Models\CollectionWash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CollectionWash>
 */
class CollectionWashFactory extends Factory
{
    public function definition(): array
    {
        return [
            'collection_id' => Collection::factory(),
            'wash' => fake()->randomElement(array_values(CollectionWash::INDEXABLE)),
            'intro' => 'Le noir de la maison est teint dans la masse : il garde sa profondeur lavage après lavage.',
            'faq' => [
                ['question' => 'Le noir déteint-il ?', 'answer' => 'Les premiers lavages libèrent un peu d’indigo. Lavez à 30 degrés, à l’envers, séparément la première fois.'],
            ],
            'meta_title' => null,
            'meta_description' => null,
            'is_visible' => true,
            'position' => 0,
        ];
    }
}
