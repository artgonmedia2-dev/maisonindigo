<?php

namespace Database\Factories;

use App\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Author>
 */
class AuthorFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->name();

        return [
            'slug' => Str::slug($name),
            'name' => $name,
            'role' => 'Responsable produit',
            'bio' => 'Suit le denim, les coupes et les tailles chez Maison Indigo.',
            'email' => null,
        ];
    }
}
