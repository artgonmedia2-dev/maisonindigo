<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

/**
 * Supprime les enregistrements d’images dont le fichier a disparu du disque.
 *
 * Une remise en ligne qui recrée le dossier storage laisse la base intacte :
 * les fiches produits gardent alors des images fantômes, visibles dans le
 * back-office comme des vignettes grises. Cette commande remet les deux en
 * accord, pour qu’il ne reste qu’à reverser les photos.
 *
 *   php artisan mi:media-prune           liste les images fantômes
 *   php artisan mi:media-prune --force   les supprime sans confirmation
 */
class MediaPruneCommand extends Command
{
    protected $signature = 'mi:media-prune {--force : Supprime sans demander confirmation}';

    protected $description = 'Supprime les images dont le fichier n’existe plus sur le disque';

    public function handle(): int
    {
        $orphelines = Media::query()->get()->filter(
            fn (Media $media): bool => ! $this->fichierPresent($media)
        );

        if ($orphelines->isEmpty()) {
            $this->components->info('Chaque image de la base a bien son fichier sur le disque.');

            return self::SUCCESS;
        }

        $this->components->warn($orphelines->count().' image(s) sans fichier :');

        foreach ($orphelines as $media) {
            $this->components->twoColumnDetail(
                "{$media->model_type} #{$media->model_id}",
                "<fg=gray>{$media->getPathRelativeToRoot()}</>",
            );
        }

        if (! $this->option('force') && ! $this->confirm('Supprimer ces enregistrements ?', false)) {
            $this->components->info('Rien n’a été supprimé.');

            return self::SUCCESS;
        }

        $orphelines->each(fn (Media $media) => $media->delete());

        $this->components->info(
            $orphelines->count().' enregistrement(s) supprimé(s). '
                .'Reversez les photos depuis le back-office.'
        );

        return self::SUCCESS;
    }

    private function fichierPresent(Media $media): bool
    {
        try {
            return is_file($media->getPath());
        } catch (Throwable) {
            // Disque distant : impossible de vérifier, on ne supprime rien.
            return true;
        }
    }
}
