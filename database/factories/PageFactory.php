<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    public function definition(): array
    {
        $title = ucfirst(fake()->unique()->words(3, true));

        return [
            'slug' => Str::slug($title),
            'title' => $title,
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['content' => fake()->paragraph()]],
            ],
            'meta_title' => null,
            'meta_description' => null,
            'is_published' => true,
        ];
    }
}
