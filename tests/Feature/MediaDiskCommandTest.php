<?php

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('local');
});

/** Crée un média rangé sur le disque indiqué, fichier compris. */
function mediaSurDisque(Product $product, string $disk): Media
{
    $media = Media::query()->create([
        'model_type' => $product->getMorphClass(),
        'model_id' => $product->getKey(),
        'uuid' => (string) Str::uuid(),
        'collection_name' => Product::MEDIA_GALLERY,
        'name' => 'face',
        'file_name' => 'face.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => $disk,
        'conversions_disk' => $disk,
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => ['view' => 'face'],
        'generated_conversions' => [],
        'responsive_images' => [],
        'order_column' => 1,
    ]);

    Storage::disk($disk)->put($media->getPathRelativeToRoot(), 'original');

    return $media;
}

it('ne touche à rien quand tout est déjà au bon endroit', function () {
    mediaSurDisque(Product::factory()->create(), 'public');

    $this->artisan('mi:media-disk')
        ->expectsOutputToContain('déjà sur le disque')
        ->assertSuccessful();
});

it('rapatrie une image écrite hors du web', function () {
    $media = mediaSurDisque(Product::factory()->create(), 'local');
    $chemin = $media->getPathRelativeToRoot();

    $this->artisan('mi:media-disk --force')->assertSuccessful();

    Storage::disk('public')->assertExists($chemin);
    Storage::disk('local')->assertMissing($chemin);

    expect($media->fresh()->disk)->toBe('public')
        ->and($media->fresh()->conversions_disk)->toBe('public');
});

it('ne déplace rien sans confirmation', function () {
    $media = mediaSurDisque(Product::factory()->create(), 'local');

    $this->artisan('mi:media-disk')
        ->expectsConfirmation('Les déplacer maintenant ?', 'no')
        ->assertSuccessful();

    expect($media->fresh()->disk)->toBe('local');
    Storage::disk('public')->assertMissing($media->getPathRelativeToRoot());
});
