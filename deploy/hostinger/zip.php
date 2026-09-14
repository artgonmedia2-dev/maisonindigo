<?php

/*
 * Compresse un dossier en archive ZIP, sans dépendre de l'utilitaire « zip »
 * (absent de Git Bash sous Windows).
 *
 *   php deploy/hostinger/zip.php <dossier-source> <archive.zip>
 */

if ($argc !== 3) {
    fwrite(STDERR, "Usage : php zip.php <dossier-source> <archive.zip>\n");
    exit(1);
}

[$source, $target] = [realpath($argv[1]), $argv[2]];

if ($source === false || ! is_dir($source)) {
    fwrite(STDERR, "Dossier introuvable : {$argv[1]}\n");
    exit(1);
}

if (! class_exists(ZipArchive::class)) {
    fwrite(STDERR, "L'extension zip de PHP est absente.\n");
    exit(1);
}

$zip = new ZipArchive;

if ($zip->open($target, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, "Impossible de créer l'archive : {$target}\n");
    exit(1);
}

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST,
);

$count = 0;

foreach ($files as $file) {
    /** @var SplFileInfo $file */
    $path = $file->getRealPath();

    if ($path === false) {
        continue;
    }

    $relative = str_replace('\\', '/', substr($path, strlen($source) + 1));

    if ($relative === '' || str_ends_with($relative, '/.DS_Store')) {
        continue;
    }

    if ($file->isDir()) {
        $zip->addEmptyDir($relative);

        continue;
    }

    $zip->addFile($path, $relative);
    $count++;
}

$zip->close();

printf("%d fichiers compressés.\n", $count);
