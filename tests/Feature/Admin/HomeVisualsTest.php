<?php

use App\Enums\HomeSlot;
use App\Filament\Resources\Collections\Pages\EditCollection;
use App\Models\Admin;
use App\Models\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(Admin::factory()->create(), 'admin');
    Storage::fake('public');
});

it('met une collection en avant sur l’accueil avec son visuel', function () {
    $collection = Collection::factory()->create();

    Livewire::test(EditCollection::class, ['record' => $collection->getRouteKey()])
        ->fillForm([
            'cover' => [UploadedFile::fake()->image('femme.jpg', 800, 1000)],
            'home_slot' => HomeSlot::Women->value,
            'home_badge' => 'limited',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $collection->refresh();

    expect($collection->home_slot)->toBe(HomeSlot::Women)
        ->and($collection->home_badge)->toBe('limited')
        ->and($collection->getFirstMedia(Collection::MEDIA_COVER))->not->toBeNull();

    $this->get('/')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('collections.women.badge', 'limited')
        ->whereNot('collections.women.image', null)
    );
});

it('refuse deux collections sur le même emplacement', function () {
    Collection::factory()->create(['home_slot' => HomeSlot::Women]);
    $autre = Collection::factory()->create();

    Livewire::test(EditCollection::class, ['record' => $autre->getRouteKey()])
        ->fillForm(['home_slot' => HomeSlot::Women->value])
        ->call('save')
        ->assertHasFormErrors(['home_slot']);
});

it('revient au gabarit denim sans collection mise en avant', function () {
    $this->get('/')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('collections.women.image', null)
        ->where('collections.women.badge', null)
        ->where('collections.men.image', null)
    );
});

it('garde le badge quand la collection n’a pas encore de visuel', function () {
    Collection::factory()->create(['home_slot' => HomeSlot::Men, 'home_badge' => 'atelier']);

    $this->get('/')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('collections.men.image', null)
        ->where('collections.men.badge', 'atelier')
    );
});
