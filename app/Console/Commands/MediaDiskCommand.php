<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

/**
 * Rapatrie les images stockées sur le mauvais disque.
 *
 * Filament suit FILESYSTEM_DISK quand aucun disque ne lui est imposé : avec
 * FILESYSTEM_DISK=local, les photos partent dans storage/app/private, que le
 * web ne sert pas. L'adresse /storage/… renvoie alors 404 alors que le fichier
 * existe bien. Cette commande les remet sur le disque des médias.
 *
 *   php artisan mi:media-disk           liste les images mal rangées
 *   php artisan mi:media-disk --force   les déplace sans confirmation
 */
class MediaDiskCommand extends Command
{
    protected $signature = 'mi:media-disk {--force : Déplace sans demander confirmation}';

    protected $description = 'Replace les images sur le disque public attendu';

    public function handle(): int
    {
        /** @var string $cible */
        $cible = config('media-library.disk_name');

        $egarees = Media::query()
            ->where(fn ($query) => $query->where('disk', '!=', $cible)->orWhere('conversions_disk', '!=', $cible))
            ->get();

        if ($egarees->isEmpty()) {
            $this->components->info("Toutes les images sont déjà sur le disque « {$cible} ».");

            return self::SUCCESS;
        }

        $this->components->warn($egarees->count()." image(s) hors du disque « {$cible} » :");

        foreach ($egarees as $media) {
            $this->components->twoColumnDetail(
                $media->getPathRelativeToRoot(),
                "<fg=gray>{$media->disk}</> → <fg=green>{$cible}</>",
            );
        }

        if (! $this->option('force') && ! $this->confirm('Les déplacer maintenant ?', false)) {
            $this->components->info('Rien n’a été déplacé.');

            return self::SUCCESS;
        }

        $deplacees = 0;

        foreach ($egarees as $media) {
            if ($this->deplacer($media, $cible)) {
                $deplacees++;
            }
        }

        $this->components->info(
            $deplacees.' image(s) déplacée(s). '
                .'Lancez php artisan media-library:regenerate --force pour recréer les vignettes.'
        );

        return $deplacees === $egarees->count() ? self::SUCCESS : self::FAILURE;
    }

    private function deplacer(Media $media, string $cible): bool
    {
        $chemin = $media->getPathRelativeToRoot();

        try {
            $source = Storage::disk($media->disk);

            if (! $source->exists($chemin)) {
                $this->components->error("Fichier introuvable : {$chemin}");

                return false;
            }

            // Copie d'abord, bascule ensuite : une interruption ne perd rien.
            Storage::disk($cible)->writeStream($chemin, $source->readStream($chemin));

            $media->forceFill(['disk' => $cible, 'conversions_disk' => $cible])->save();

            $source->deleteDirectory((string) $media->id);

            return true;
        } catch (Throwable $exception) {
            $this->components->error("{$chemin} : {$exception->getMessage()}");

            return false;
        }
    }
}
