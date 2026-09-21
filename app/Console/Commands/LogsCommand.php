<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use SplFileInfo;

/**
 * Affiche les dernières erreurs du journal, sans avoir à trouver le fichier.
 *
 *   php artisan mi:logs            les 5 dernières erreurs
 *   php artisan mi:logs --lines=3  les 3 dernières
 *   php artisan mi:logs --full     la trace complète de la dernière
 */
class LogsCommand extends Command
{
    protected $signature = 'mi:logs {--lines=5 : Nombre d’erreurs à afficher} {--full : Trace complète}';

    protected $description = 'Affiche les dernières erreurs enregistrées par la boutique';

    public function handle(): int
    {
        $file = $this->latestLog();

        if ($file === null) {
            $this->components->info('Aucun journal : la boutique n’a rien eu à signaler.');

            return self::SUCCESS;
        }

        $this->components->twoColumnDetail('Journal', $file->getFilename());
        $this->components->twoColumnDetail('Taille', number_format($file->getSize() / 1024, 1, ',', ' ').' ko');
        $this->newLine();

        $entries = $this->entries($file->getPathname(), (int) $this->option('lines'));

        if ($entries === []) {
            $this->components->info('Aucune erreur enregistrée dans ce journal.');

            return self::SUCCESS;
        }

        foreach ($entries as $entry) {
            $lines = explode("\n", $entry);
            $headline = trim(array_shift($lines) ?? '');

            $this->line('  <fg=red>'.$headline.'</>');

            if ($this->option('full')) {
                foreach ($lines as $line) {
                    $this->line('    <fg=gray>'.rtrim($line).'</>');
                }
            } else {
                // Les deux premières lignes de trace suffisent à situer la cause.
                foreach (array_slice($lines, 0, 2) as $line) {
                    $this->line('    <fg=gray>'.rtrim($line).'</>');
                }
            }

            $this->newLine();
        }

        $this->components->warn('Relancez avec --full pour la trace complète.');

        return self::SUCCESS;
    }

    private function latestLog(): ?SplFileInfo
    {
        $directory = storage_path('logs');

        if (! File::isDirectory($directory)) {
            return null;
        }

        $files = collect(File::files($directory))
            ->filter(fn (SplFileInfo $file): bool => str_ends_with($file->getFilename(), '.log') && $file->getSize() > 0)
            ->sortByDesc(fn (SplFileInfo $file): int => $file->getMTime());

        return $files->first();
    }

    /**
     * Découpe le journal par entrée horodatée et garde les dernières erreurs.
     *
     * @return list<string>
     */
    private function entries(string $path, int $limit): array
    {
        $content = (string) File::get($path);

        $parts = preg_split('/^(?=\[\d{4}-\d{2}-\d{2})/m', $content, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $errors = array_values(array_filter(
            $parts,
            fn (string $part): bool => (bool) preg_match('/\.(ERROR|CRITICAL|ALERT|EMERGENCY):/', $part),
        ));

        return array_slice($errors, -max(1, $limit));
    }
}
