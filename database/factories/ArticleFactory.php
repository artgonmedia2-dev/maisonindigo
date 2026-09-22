<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    public function definition(): array
    {
        $title = Str::ucfirst(fake()->unique()->sentence(5));

        return [
            'slug' => Str::slug($title),
            'title' => $title,
            'excerpt' => 'Un baggy tombe ample de la cuisse à la cheville, sans traîner au sol. Comptez une ouverture de jambe de 24 cm et votre taille habituelle.',
            'content_blocks' => [
                ['title' => 'Comment tombe la coupe', 'body' => 'Section à compléter dans le back-office.'],
            ],
            'faq' => [
                ['question' => 'Un baggy taille-t-il grand ?', 'answer' => 'Il tombe ample par construction : prenez votre taille habituelle.'],
            ],
            'author_id' => Author::factory(),
            'published_at' => now()->subDay(),
            'meta_title' => null,
            'meta_description' => null,
            'position' => fake()->numberBetween(1, 100),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['published_at' => null]);
    }
}
