<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Crée ou met à jour le compte administrateur défini dans .env
 * (ADMIN_NAME, ADMIN_EMAIL, ADMIN_PASSWORD).
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) config('maison.admin.email');
        $password = (string) config('maison.admin.password');

        if ($email === '' || $password === '') {
            throw new RuntimeException('Renseignez ADMIN_EMAIL et ADMIN_PASSWORD dans .env avant de lancer AdminSeeder.');
        }

        Admin::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => (string) config('maison.admin.name', 'Maison Indigo'),
                'password' => $password,
                'email_verified_at' => now(),
            ],
        );
    }
}
