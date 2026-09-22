<?php

use App\Enums\Gender;
use App\Models\Product;
use Inertia\Testing\AssertableInertia as Assert;

it('affiche le quiz sans résultat', function () {
    $this->get(route('size-quiz'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('SizeQuiz/Index')
            ->where('result', null)
            ->where('answers', null)
            ->has('options.genders', 2)
            ->has('options.hips', 3)
            ->has('options.fits', 3)
        );
});

it('recommande une coupe, une taille et une longueur, puis trois modèles', function () {
    Product::factory()->withVariants(sizes: [30, 32, 34])->create(['gender' => Gender::Homme, 'cut' => 'straight', 'wash' => 'brut']);
    Product::factory()->withVariants(sizes: [30, 32, 34])->create(['gender' => Gender::Homme, 'cut' => 'straight', 'wash' => 'noir']);
    Product::factory()->withVariants(sizes: [30, 32, 34])->create(['gender' => Gender::Homme, 'cut' => 'regular', 'wash' => 'stone']);

    $this->post(route('size-quiz.store'), ['gender' => 'homme', 'waist_cm' => 81, 'height_cm' => 175, 'hips' => 'moyennes', 'fit' => 'droit'])
        ->assertRedirect(route('size-quiz'));

    $this->get(route('size-quiz'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('result.cut', 'straight')
            ->where('result.size', 32)
            ->where('result.length', 32)
            ->where('result.label', '32 / 32')
            ->where('result.alternative_cut', 'regular')
            ->where('answers.waist_cm', 81)
            ->has('products', 3)
            ->where('products.0.cut', 'straight')
        );
});

it('adapte la coupe aux hanches et au tombé', function () {
    $this->post(route('size-quiz.store'), ['gender' => 'femme', 'waist_cm' => 71, 'height_cm' => 160, 'hips' => 'larges', 'fit' => 'ajuste']);

    $this->get(route('size-quiz'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('result.cut', 'bootcut')
            ->where('result.size', 29)
            ->where('result.length', 30)
        );
});

it('valide les réponses', function () {
    $this->post(route('size-quiz.store'), ['gender' => 'enfant', 'waist_cm' => 20, 'height_cm' => 300, 'hips' => 'x', 'fit' => 'y'])
        ->assertSessionHasErrors(['gender', 'waist_cm', 'height_cm', 'hips', 'fit']);
});

it('efface le résultat', function () {
    $this->post(route('size-quiz.store'), ['gender' => 'homme', 'waist_cm' => 81, 'height_cm' => 175, 'hips' => 'moyennes', 'fit' => 'droit']);
    $this->delete(route('size-quiz.reset'))->assertRedirect(route('size-quiz'));

    $this->get(route('size-quiz'))->assertInertia(fn (Assert $page) => $page->where('result', null));
});
