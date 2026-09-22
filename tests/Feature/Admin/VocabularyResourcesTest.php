<?php

use App\Enums\Gender;
use App\Filament\Resources\Cuts\Pages\CreateCut;
use App\Filament\Resources\Cuts\Pages\EditCut;
use App\Filament\Resources\Washes\Pages\CreateWash;
use App\Models\Admin;
use App\Models\Cut;
use App\Models\Product;
use App\Models\Wash;
use Livewire\Livewire;

beforeEach(fn () => $this->actingAs(Admin::factory()->create(), 'admin'));

it('crée une coupe depuis le back-office', function () {
    Livewire::test(CreateCut::class)
        ->fillForm([
            'name' => 'Carrot',
            'slug' => 'carrot',
            'sku_code' => 'car',
            'genders' => [Gender::Homme->value],
            'position' => 120,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $cut = Cut::query()->where('slug', 'carrot')->firstOrFail();

    // Le code SKU est normalisé : il part tel quel dans la référence.
    expect($cut->sku_code)->toBe('CAR')
        ->and($cut->genders)->toBe([Gender::Homme->value])
        ->and($cut->isAvailableFor(Gender::Femme))->toBeFalse();
});

it('crée un lavage depuis le back-office', function () {
    Livewire::test(CreateWash::class)
        ->fillForm(['name' => 'Indigo nuit', 'slug' => 'indigo_nuit', 'sku_code' => 'IND', 'position' => 70])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Wash::query()->where('slug', 'indigo_nuit')->exists())->toBeTrue();
});

it('refuse deux coupes avec le même identifiant', function () {
    Livewire::test(CreateCut::class)
        ->fillForm([
            'name' => 'Droite',
            'slug' => 'straight',
            'sku_code' => 'DRO',
            'genders' => [Gender::Homme->value],
            'position' => 10,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});

it('verrouille l’identifiant d’une coupe employée', function () {
    Product::factory()->create(['gender' => Gender::Homme, 'cut' => 'straight', 'wash' => 'brut']);

    $cut = Cut::query()->where('slug', 'straight')->firstOrFail();

    Livewire::test(EditCut::class, ['record' => $cut->getRouteKey()])
        ->assertFormFieldIsDisabled('slug')
        ->assertFormFieldIsEnabled('name');
});

it('laisse renommer une coupe sans toucher aux produits', function () {
    $product = Product::factory()->create(['gender' => Gender::Homme, 'cut' => 'straight', 'wash' => 'brut']);
    $cut = Cut::query()->where('slug', 'straight')->firstOrFail();

    Livewire::test(EditCut::class, ['record' => $cut->getRouteKey()])
        ->fillForm(['name' => 'Droite'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->fresh()->cut)->toBe('straight')
        ->and($product->fresh()->cutLabel())->toBe('Droite');
});
