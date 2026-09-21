<?php

namespace App\Http\Resources;

use App\Models\Product;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

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
            ->map(fn (Media $media): ?array => self::fromMedia($media, $product))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array{src: string, srcset: string, avif_srcset: string, alt: string, view: string}|null
     */
    private static function fromMedia(Media $media, Product $product): ?array
    {
        // Sans fichier d’origine, il n’y a rien à montrer : mieux vaut le gabarit
        // d’attente qu’une image cassée. Cela arrive quand la base a survécu à une
        // remise en ligne qui a emporté le dossier storage.
        if (! self::fichierPresent($media, '')) {
            return null;
        }

        /** @var string $view */
        $view = $media->getCustomProperty('view', Product::VIEWS[0]);

        $hasCard = self::hasConversion($media, 'card');
        $hasAvif = self::hasConversion($media, 'card-avif');

        return [
            // Sans conversion générée, l'original reste affichable.
            'src' => $hasCard ? $media->getUrl('card') : $media->getUrl(),
            'srcset' => $hasCard ? $media->getSrcset('card') : '',
            'avif_srcset' => $hasAvif ? $media->getSrcset('card-avif') : '',
            'alt' => "{$product->title}, {$product->gender->getLabel()}, vue {$view}",
            'view' => $view,
        ];
    }

    /**
     * Une conversion n’est utilisable que si la base la déclare générée ET que
     * le fichier existe vraiment. Une file d’attente interrompue, un transfert
     * incomplet ou un dossier storage recréé laissent la colonne à true alors
     * que l’image a disparu : le front afficherait alors une image cassée.
     */
    private static function hasConversion(Media $media, string $conversion): bool
    {
        return $media->hasGeneratedConversion($conversion)
            && self::fichierPresent($media, $conversion);
    }

    /**
     * @param  string  $conversion  Vide pour le fichier d’origine.
     */
    private static function fichierPresent(Media $media, string $conversion): bool
    {
        try {
            return is_file($media->getPath($conversion));
        } catch (Throwable) {
            // Disque distant : impossible de vérifier, on fait confiance à la base.
            return true;
        }
    }
}
