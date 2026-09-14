<?php

namespace App\Http\Resources;

use App\Models\Product;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Images d'un produit pour le front : conversions WebP/AVIF et srcset.
 * Sans média (démo), retourne null : le front affiche le gabarit d'attente.
 */
final class ProductImageResource
{
    /**
     * @return array{src: string, srcset: string, avif_srcset: string, alt: string, view: string}|null
     */
    public static function first(Product $product): ?array
    {
        /** @var Media|null $media */
        $media = $product->getFirstMedia(Product::MEDIA_GALLERY);

        return $media === null ? null : self::fromMedia($media, $product);
    }

    /**
     * @return list<array{src: string, srcset: string, avif_srcset: string, alt: string, view: string}>
     */
    public static function all(Product $product): array
    {
        return $product->getMedia(Product::MEDIA_GALLERY)
            ->map(fn (Media $media): array => self::fromMedia($media, $product))
            ->values()
            ->all();
    }

    /**
     * @return array{src: string, srcset: string, avif_srcset: string, alt: string, view: string}
     */
    private static function fromMedia(Media $media, Product $product): array
    {
        /** @var string $view */
        $view = $media->getCustomProperty('view', Product::VIEWS[0]);

        return [
            'src' => $media->getUrl('card'),
            'srcset' => $media->getSrcset('card'),
            'avif_srcset' => $media->getSrcset('card-avif'),
            'alt' => "{$product->title}, {$product->gender->getLabel()}, vue {$view}",
            'view' => $view,
        ];
    }
}
