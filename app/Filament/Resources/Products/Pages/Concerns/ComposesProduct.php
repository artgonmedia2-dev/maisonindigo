<?php

namespace App\Filament\Resources\Products\Pages\Concerns;

use App\Enums\Gender;
use App\Models\Product;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Titre et adresse composés depuis coupe, lavage et genre ; les six vues
 * de la galerie nommées d'après leur ordre (face, dos, profil, tissu, détail, porté).
 */
trait ComposesProduct
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function composeTitleAndSlug(array $data): array
    {
        $title = Product::composeTitle($data['cut'] ?? null, $data['wash'] ?? null);

        if ($title !== '') {
            $data['title'] = $title;

            if (blank($data['slug'] ?? null)) {
                $gender = $data['gender'] ?? null;
                $gender = $gender instanceof Gender ? $gender->value : (string) $gender;

                $data['slug'] = $this->uniqueSlug(Str::slug($title.' '.$gender));
            }
        }

        return $data;
    }

    private function uniqueSlug(string $slug): string
    {
        $record = $this->getRecord();
        $candidate = $slug;
        $suffix = 1;

        while (Product::query()
            ->where('slug', $candidate)
            ->when($record !== null, fn ($query) => $query->whereKeyNot($record->getKey()))
            ->exists()) {
            $candidate = $slug.'-'.++$suffix;
        }

        return $candidate;
    }

    protected function nameMediaViews(): void
    {
        /** @var Product $product */
        $product = $this->getRecord();

        $product->getMedia(Product::MEDIA_GALLERY)
            ->values()
            ->each(function (Media $media, int $index): void {
                $view = Product::VIEWS[$index] ?? 'detail';

                if ($media->getCustomProperty('view') !== $view) {
                    $media->setCustomProperty('view', $view);
                    $media->save();
                }
            });
    }
}
