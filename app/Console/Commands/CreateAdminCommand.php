<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

/**
 * Crée un administrateur du back-office, ou réinitialise son mot de passe.
 *
 *   php artisan mi:admin youssef@maisonindigo.ma "MotDePasseSolide" --name=Youssef
 *   php artisan mi:admin                      (l'adresse et le mot de passe sont demandés)
 */
class CreateAdminCommand extends Command
{
    protected $signature = 'mi:admin
        {email? : Adresse e-mail de connexion}
        {password? : Mot de passe, huit caractères au moins}
        {--name= : Nom affiché dans le back-office}';

    protected $description = 'Crée un administrateur du back-office ou réinitialise son mot de passe';

    public function handle(): int
    {
        $email = $this->argument('email') ?? $this->ask('Adresse e-mail de connexion');
        $password = $this->argument('password') ?? $this->secret('Mot de passe');
        $name = $this->option('name') ?? (string) config('maison.admin.name', 'Maison Indigo');

        $validator = Validator::make(
            ['email' => $email, 'password' => $password, 'name' => $name],
            [
                'email' => ['required', 'email:rfc', 'max:190'],
                'password' => ['required', 'string', 'min:8'],
                'name' => ['required', 'string', 'max:120'],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->components->error($message);
            }

            return self::FAILURE;
        }

        $existed = Admin::query()->where('email', $email)->exists();

        Admin::query()->updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $password],
        );

        $this->components->info($existed
            ? "Mot de passe réinitialisé pour {$email}."
            : "Administrateur {$email} créé.");

        $this->components->twoColumnDetail('Connexion', rtrim((string) config('app.url'), '/').'/admin');
        $this->components->twoColumnDetail('Identifiant', (string) $email);

        return self::SUCCESS;
    }
}
