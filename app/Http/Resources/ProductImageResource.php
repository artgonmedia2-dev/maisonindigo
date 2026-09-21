<?php

namespace App\Http\Resources;

use App\Models\Product;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Images d'un produit pour le front : conversions WebP et AVIF, avec srcset.
 *
 * Chaque variante n'est annoncée que si elle a réellement été générée. Un
 * navigateur qui choisit un <source> absent affiche une image cassée sans
 * revenir au <img> de repli : mieux vaut ne rien proposer que du vide.
 * Sans média, retourne null et le front affiche le gabarit d'attente.
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

        $hasCard = $media->hasGeneratedConversion('card');
        $hasAvif = $media->hasGeneratedConversion('card-avif');

        return [
            // Sans conversion générée, l'original reste affichable.
            'src' => $hasCard ? $media->getUrl('card') : $media->getUrl(),
            'srcset' => $hasCard ? $media->getSrcset('card') : '',
            'avif_srcset' => $hasAvif ? $media->getSrcset('card-avif') : '',
            'alt' => "{$product->title}, {$product->gender->getLabel()}, vue {$view}",
            'view' => $view,
        ];
    }
}
