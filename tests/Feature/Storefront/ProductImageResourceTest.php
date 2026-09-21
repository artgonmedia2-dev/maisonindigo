<?php

use App\Http\Resources\ProductImageResource;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Attache un média au produit sans passer par un vrai téléversement : seules
 * comptent les conversions déclarées générées et les fichiers réellement posés.
 *
 * @param  array<string, bool>  $generated
 * @param  array<string, array{urls: list<string>}>  $responsive
 * @param  list<string>  $onDisk  Conversions dont le fichier existe vraiment.
 */
function attachMedia(Product $product, array $generated, array $responsive = [], ?array $onDisk = null): Media
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
        'generated_conversions' => $generated,
        'responsive_images' => $responsive,
        'order_column' => 1,
    ]);

    $disk = Storage::disk('public');
    $disk->put($media->getPathRelativeToRoot(), 'original');

    foreach ($onDisk ?? array_keys(array_filter($generated)) as $conversion) {
        $disk->put($media->getPathRelativeToRoot($conversion), 'conversion');
    }

    return $media;
}

beforeEach(fn () => Storage::fake('public'));

it('n’annonce l’AVIF que lorsqu’il a été généré', function () {
    $product = Product::factory()->create();
    attachMedia($product, ['card' => true, 'card-avif' => true], [
        'card' => ['urls' => ['face___card_800_1072.webp']],
        'card-avif' => ['urls' => ['face___card-avif_800_1072.avif']],
    ]);

    $image = ProductImageResource::first($product->fresh());

    expect($image)->not->toBeNull()
        ->and($image['avif_srcset'])->toContain('.avif')
        ->and($image['srcset'])->toContain('.webp')
        ->and($image['src'])->toContain('conversions');
});

it('retombe sur le WebP quand l’AVIF manque', function () {
    $product = Product::factory()->create();
    attachMedia($product, ['card' => true], [
        'card' => ['urls' => ['face___card_800_1072.webp']],
    ]);

    $image = ProductImageResource::first($product->fresh());

    // Un <source type="image/avif"> absent du disque casserait l'affichage :
    // le front ne doit donc rien recevoir à annoncer.
    expect($image['avif_srcset'])->toBe('')
        ->and($image['srcset'])->toContain('.webp')
        ->and($image['src'])->toContain('conversions');
});

it('retombe sur l’original quand aucune conversion n’existe', function () {
    $product = Product::factory()->create();
    attachMedia($product, []);

    $image = ProductImageResource::first($product->fresh());

    expect($image['avif_srcset'])->toBe('')
        ->and($image['srcset'])->toBe('')
        ->and($image['src'])->toContain('face.jpg')
        ->and($image['src'])->not->toContain('conversions')
        ->and($image['alt'])->toContain('vue face');
});

it('retombe sur l’original quand la base annonce une conversion absente du disque', function () {
    $product = Product::factory()->create();

    // Cas vécu en production : file d'attente interrompue, dossier storage
    // recréé ou transfert incomplet. La colonne dit « généré », le fichier non.
    attachMedia($product, ['card' => true, 'card-avif' => true], [
        'card' => ['urls' => ['face___card_800_1072.webp']],
    ], onDisk: []);

    $image = ProductImageResource::first($product->fresh());

    expect($image['src'])->toContain('face.jpg')
        ->and($image['src'])->not->toContain('conversions')
        ->and($image['srcset'])->toBe('')
        ->and($image['avif_srcset'])->toBe('');
});

it('ignore un média dont le fichier d’origine a disparu', function () {
    $product = Product::factory()->create();
    $media = attachMedia($product, ['card' => true]);

    Storage::disk('public')->delete($media->getPathRelativeToRoot());

    // Le gabarit d’attente vaut mieux qu’une image cassée.
    expect(ProductImageResource::first($product->fresh()))->toBeNull()
        ->and(ProductImageResource::all($product->fresh()))->toBe([]);
});

it('ne renvoie rien sans média', function () {
    expect(ProductImageResource::first(Product::factory()->create()))->toBeNull();
});
