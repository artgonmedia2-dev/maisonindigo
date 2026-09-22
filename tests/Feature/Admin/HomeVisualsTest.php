<?php

use App\Filament\Pages\ManageShopSettings;
use App\Models\Admin;
use App\Settings\ShopSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(Admin::factory()->create(), 'admin');
    Storage::fake('public');
});

it('dépose le visuel d’une collection sur le disque public', function () {
    Livewire::test(ManageShopSettings::class)
        ->fillForm(['home_women_image' => [UploadedFile::fake()->image('femme.jpg', 800, 1000)]])
        ->call('save')
        ->assertHasNoFormErrors();

    $chemin = app(ShopSettings::class)->refresh()->home_women_image;

    // Le réglage doit tenir un chemin, pas un tableau : le front le passe à url().
    expect($chemin)->toBeString()
        ->and($chemin)->toStartWith('accueil/');

    Storage::disk('public')->assertExists($chemin);
});

it('change le badge d’une collection', function () {
    Livewire::test(ManageShopSettings::class)
        ->fillForm(['home_women_badge' => 'atelier', 'home_men_badge' => null])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = app(ShopSettings::class)->refresh();

    expect($settings->home_women_badge)->toBe('atelier')
        ->and($settings->home_men_badge)->toBeNull();
});
