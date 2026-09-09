<?php

namespace Database\Factories;

use App\Enums\CollectionType;
use App\Models\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Collection>
 */
class CollectionFactory extends Factory
{
    public function definition(): array
    {
        $title = ucfirst(fake()->unique()->words(2, true));

        return [
            'slug' => Str::slug($title),
            'title' => $title,
            'description' => fake()->sentence(),
            'type' => CollectionType::Manual,
            'rules' => null,
            'position' => 0,
            'is_visible' => true,
            'meta_title' => null,
            'meta_description' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $rules
     */
    public function ruled(array $rules): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CollectionType::Rule,
            'rules' => $rules,
        ]);
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => ['is_visible' => false]);
    }
}
