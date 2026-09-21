<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\Product;
use App\Models\ShippingZone;
use App\Settings\ShopSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

/**
 * Contrôle avant et après mise en ligne : ce qui bloque, ce qui mérite un regard.
 *
 *   php artisan mi:deploy-check
 *
 * Code de sortie 1 dès qu'un point bloquant échoue, pour être utilisable en CI
 * ou dans un script de déploiement.
 */
class DeployCheckCommand extends Command
{
    protected $signature = 'mi:deploy-check {--strict : Les avertissements deviennent bloquants}';

    protected $description = 'Vérifie que le serveur est prêt à servir la boutique';

    /** @var list<string> */
    private const EXTENSIONS = ['pdo_mysql', 'mbstring', 'intl', 'gd', 'exif', 'fileinfo', 'zip', 'curl', 'openssl'];

    private int $failures = 0;

    private int $warnings = 0;

    public function handle(): int
    {
        $this->components->info('Contrôle de mise en ligne — Maison Indigo');

        $this->checkPhp();
        $this->checkEnvironment();
        $this->checkDatabase();
        $this->checkFilesystem();
        $this->checkAssets();
        $this->checkContent();

        $this->newLine();

        if ($this->failures > 0) {
            $this->components->error("{$this->failures} point(s) bloquant(s). Le site ne fonctionnera pas correctement.");

            return self::FAILURE;
        }

        if ($this->warnings > 0) {
            $this->components->warn("{$this->warnings} point(s) à surveiller.");

            return $this->option('strict') ? self::FAILURE : self::SUCCESS;
        }

        $this->components->info('Tout est en ordre. La boutique peut ouvrir.');

        return self::SUCCESS;
    }

    private function checkPhp(): void
    {
        $this->line('  <fg=gray>PHP</>');

        // Sous PHP 8.2, Composer arrête l'application avant même d'arriver ici :
        // le contrôle utile est ailleurs, la version du site pouvant différer de celle-ci.
        $this->components->twoColumnDetail('Version en ligne de commande', '<fg=green>'.PHP_VERSION.'</>');

        $this->warnIf(
            'Version du site identique',
            false,
            'Vérifiez dans hPanel → Avancé → Configuration PHP que le site tourne aussi en 8.3 : une version 8.2 donne une page blanche sans journal.',
        );

        foreach (self::EXTENSIONS as $extension) {
            $this->assert("Extension {$extension}", extension_loaded($extension), "Activez {$extension} dans hPanel → Configuration PHP → Extensions.");
        }

        $this->warnIf(
            'Mémoire disponible',
            $this->memoryLimitInBytes() >= 256 * 1024 * 1024 || $this->memoryLimitInBytes() === -1,
            'memory_limit inférieur à 256M : les conversions d’images peuvent échouer.',
        );
    }

    private function checkEnvironment(): void
    {
        $this->line('  <fg=gray>Environnement</>');

        $this->assert('Clé d’application', filled(config('app.key')), 'Lancez php artisan key:generate --force.');

        $this->assert(
            'Mode production',
            app()->environment('production'),
            'APP_ENV vaut « '.app()->environment().' » : passez-le à production dans .env.',
        );

        $this->assert(
            'Débogage désactivé',
            config('app.debug') === false,
            'APP_DEBUG=true expose vos identifiants sur une page d’erreur. Passez-le à false.',
        );

        $url = (string) config('app.url');

        $this->assert('Adresse du site renseignée', filled($url) && $url !== 'http://localhost', 'Renseignez APP_URL avec votre domaine complet.');
        $this->warnIf('Adresse en https', str_starts_with($url, 'https://'), 'APP_URL devrait commencer par https.');
        $this->warnIf('Adresse sans barre finale', ! str_ends_with($url, '/'), 'Retirez la barre oblique finale d’APP_URL : elle produit des liens en //.');

        $this->warnIf('Proxys de confiance', filled(config('maison.trusted_proxies')), 'TRUSTED_PROXIES vide : derrière LiteSpeed, Laravel ne verra pas le https.');
        $this->warnIf('HTTPS forcé', config('maison.force_https') === true, 'FORCE_HTTPS=false : les liens générés resteront en http.');

        $this->assert(
            'Rendu serveur cohérent',
            config('inertia.ssr.enabled') === false,
            'INERTIA_SSR_ENABLED=true sans serveur Node : chaque page perdra du temps à tenter une connexion. Passez-le à false.',
        );

        $this->warnIf(
            'File d’attente traitable',
            in_array(config('queue.default'), ['database', 'redis', 'sync'], true),
            'QUEUE_CONNECTION inattendu : les messages WhatsApp ne partiront pas.',
        );
    }

    private function checkDatabase(): void
    {
        $this->line('  <fg=gray>Base de données</>');

        try {
            DB::connection()->getPdo();
            $this->assert('Connexion', true, '');
        } catch (Throwable $exception) {
            $this->assert('Connexion', false, 'Identifiants DB_* refusés : '.$exception->getMessage());

            return;
        }

        $pending = 0;

        try {
            $ran = DB::table('migrations')->pluck('migration')->all();

            // Les migrations de réglages (spatie/laravel-settings) sont inscrites
            // dans la même table : on les compte avec les autres.
            $files = collect([database_path('migrations'), database_path('settings')])
                ->filter(fn (string $directory): bool => File::isDirectory($directory))
                ->flatMap(fn (string $directory): array => File::files($directory))
                ->map(fn ($file): string => $file->getFilenameWithoutExtension())
                ->all();

            $pending = count(array_diff($files, $ran));
        } catch (Throwable) {
            $pending = -1;
        }

        $this->assert(
            'Migrations à jour',
            $pending === 0,
            $pending === -1
                ? 'Table migrations absente : lancez php artisan migrate --force.'
                : "{$pending} migration(s) en attente : lancez php artisan migrate --force.",
        );

        try {
            $settings = app(ShopSettings::class);
            $this->assert('Réglages de la boutique', filled($settings->contact_email), 'La table settings est vide : lancez php artisan migrate --force (les réglages vivent dans database/settings).');
        } catch (Throwable $exception) {
            $this->assert('Réglages de la boutique', false, 'Réglages illisibles : '.$exception->getMessage());
        }
    }

    private function checkFilesystem(): void
    {
        $this->line('  <fg=gray>Fichiers</>');

        foreach (['storage/app', 'storage/framework/cache', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs', 'bootstrap/cache'] as $path) {
            $this->assert("Écriture dans {$path}", is_writable(base_path($path)), 'Lancez chmod -R 775 storage bootstrap/cache.');
        }

        $this->checkStorageLink();

        $this->warnIf(
            'Fichier .env hors du web',
            ! File::exists(public_path('.env')),
            'Un .env est accessible dans public/ : supprimez-le immédiatement.',
        );
    }

    /**
     * Le lien public/storage est la cause la plus fréquente d'images absentes :
     * on vérifie qu'il existe, qu'il pointe au bon endroit et qu'un fichier
     * déposé dans storage/app/public est bien lisible à travers lui.
     */
    private function checkStorageLink(): void
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        if (! File::exists($link)) {
            $this->assert('Lien public/storage', false, 'Lancez php artisan storage:link : sans lui, aucune photo produit ne s’affiche.');

            return;
        }

        if (! is_dir($link)) {
            $this->assert('Lien public/storage', false, 'public/storage existe mais n’est pas un dossier. Supprimez-le puis relancez php artisan storage:link.');

            return;
        }

        $probe = 'controle-'.bin2hex(random_bytes(4)).'.txt';

        try {
            File::ensureDirectoryExists($target);
            File::put($target.DIRECTORY_SEPARATOR.$probe, 'ok');
            $readable = File::exists($link.DIRECTORY_SEPARATOR.$probe);
        } catch (Throwable) {
            $readable = false;
        } finally {
            File::delete($target.DIRECTORY_SEPARATOR.$probe);
        }

        $this->assert(
            'Lien public/storage',
            $readable,
            'public/storage ne donne pas accès à storage/app/public. Supprimez-le puis relancez php artisan storage:link, '
                .'et vérifiez que le .htaccess autorise +FollowSymLinks.',
        );
    }

    private function checkAssets(): void
    {
        $this->line('  <fg=gray>Interface</>');

        $this->assert(
            'Assets compilés',
            File::exists(public_path('build/manifest.json')),
            'public/build absent : lancez npm run build sur votre poste, puis envoyez le dossier.',
        );

        $this->warnIf(
            'Assets Filament',
            File::exists(public_path('js/filament')),
            'Lancez php artisan filament:assets.',
        );

        $this->warnIf(
            'Polices de la maison',
            File::exists(public_path('fonts/inter-latin.woff2')),
            'public/fonts absent : la boutique retombera sur les polices du système.',
        );

        $this->warnIf(
            'Caches de production',
            File::exists(base_path('bootstrap/cache/config.php')),
            'Lancez php artisan optimize pour gagner en vitesse.',
        );
    }

    private function checkContent(): void
    {
        $this->line('  <fg=gray>Contenu</>');

        try {
            $this->assert('Un administrateur existe', Admin::query()->exists(), 'Lancez php artisan mi:admin adresse@exemple.ma "MotDePasse".');
            $this->assert('Une zone de livraison de repli', ShippingZone::query()->where('cities', '[]')->orWhereNull('cities')->exists(), 'Lancez php artisan db:seed --class=ShippingZonesSeeder --force.');
            $this->warnIf('Au moins un produit en ligne', Product::query()->active()->exists(), 'Aucun produit actif : la boutique sera vide.');
        } catch (Throwable $exception) {
            $this->assert('Lecture du catalogue', false, $exception->getMessage());
        }
    }

    private function assert(string $label, bool $passed, string $hint): void
    {
        $this->components->twoColumnDetail($label, $passed ? '<fg=green>OK</>' : '<fg=red>BLOQUANT</>');

        if (! $passed) {
            $this->failures++;

            if ($hint !== '') {
                $this->line("      <fg=red>→ {$hint}</>");
            }
        }
    }

    private function warnIf(string $label, bool $passed, string $hint): void
    {
        $this->components->twoColumnDetail($label, $passed ? '<fg=green>OK</>' : '<fg=yellow>À VOIR</>');

        if (! $passed) {
            $this->warnings++;
            $this->line("      <fg=yellow>→ {$hint}</>");
        }
    }

    private function memoryLimitInBytes(): int
    {
        $limit = (string) ini_get('memory_limit');

        if ($limit === '-1') {
            return -1;
        }

        $value = (int) $limit;

        return match (strtoupper(substr($limit, -1))) {
            'G' => $value * 1024 * 1024 * 1024,
            'M' => $value * 1024 * 1024,
            'K' => $value * 1024,
            default => $value,
        };
    }
}
