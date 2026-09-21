<?php

use App\Http\Resources\ProductImageResource;
use App\Models\Product;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Attache un média au produit sans passer par le disque : seules comptent
 * les conversions déclarées comme générées.
 *
 * @param  array<string, bool>  $generated
 * @param  array<string, array{urls: list<string>}>  $responsive
 */
function attachMedia(Product $product, array $generated, array $responsive = []): Media
{
    return Media::query()->create([
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
}

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

it('ne renvoie rien sans média', function () {
    expect(ProductImageResource::first(Product::factory()->create()))->toBeNull();
});
