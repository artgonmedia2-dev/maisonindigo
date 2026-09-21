<?php

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

beforeEach(fn () => Storage::fake('public'));

/** Crée un média, avec ou sans son fichier sur le disque. */
function mediaAvecFichier(Product $product, bool $surDisque): Media
{
    $media = Media::query()->create([
        'model_type' => $product->getMorphClass(),
        'model_id' => $product->getKey(),
        'uuid' => (string) Str::uuid(),
        'collection_name' => Product::MEDIA_GALLERY,
        'name' => 'face',
        'file_name' => 'face.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'conversions_disk' => 'public',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => ['view' => 'face'],
        'generated_conversions' => [],
        'responsive_images' => [],
        'order_column' => 1,
    ]);

    if ($surDisque) {
        Storage::disk('public')->put($media->getPathRelativeToRoot(), 'original');
    }

    return $media;
}

it('ne signale rien quand chaque fichier est là', function () {
    mediaAvecFichier(Product::factory()->create(), surDisque: true);

    $this->artisan('mi:media-prune')
        ->expectsOutputToContain('bien son fichier')
        ->assertSuccessful();

    expect(Media::query()->count())->toBe(1);
});

it('supprime les images dont le fichier a disparu', function () {
    $product = Product::factory()->create();
    $garde = mediaAvecFichier($product, surDisque: true);
    $orpheline = mediaAvecFichier($product, surDisque: false);

    $this->artisan('mi:media-prune --force')->assertSuccessful();

    expect(Media::query()->pluck('id')->all())->toBe([$garde->id])
        ->and(Media::query()->find($orpheline->id))->toBeNull();
});

it('ne supprime rien sans confirmation', function () {
    mediaAvecFichier(Product::factory()->create(), surDisque: false);

    $this->artisan('mi:media-prune')
        ->expectsConfirmation('Supprimer ces enregistrements ?', 'no')
        ->assertSuccessful();

    expect(Media::query()->count())->toBe(1);
});
