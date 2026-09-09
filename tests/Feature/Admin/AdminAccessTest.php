<?php

use App\Models\Admin;
use App\Models\Customer;
use Database\Seeders\AdminSeeder;
use Illuminate\Support\Facades\Hash;

it('redirige un visiteur vers la connexion du back-office', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('affiche la page de connexion du back-office', function () {
    $this->get('/admin/login')
        ->assertOk()
        ->assertSee('Maison Indigo');
});

it('ouvre le tableau de bord à un administrateur', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')
        ->get('/admin')
        ->assertOk();
});

it('refuse un client connecté sur le back-office', function () {
    $customer = Customer::factory()->create();

    $this->actingAs($customer)
        ->get('/admin')
        ->assertRedirect('/admin/login');
});

it('crée l’administrateur défini dans la configuration', function () {
    config()->set('maison.admin', [
        'name' => 'Youssef',
        'email' => 'youssef@maisonindigo.test',
        'password' => 'mot-de-passe-solide',
    ]);

    $this->seed(AdminSeeder::class);

    $admin = Admin::query()->where('email', 'youssef@maisonindigo.test')->first();

    expect($admin)->not->toBeNull()
        ->and($admin?->name)->toBe('Youssef')
        ->and(Hash::check('mot-de-passe-solide', (string) $admin?->password))->toBeTrue();
});

it('refuse de créer un administrateur sans identifiants', function () {
    config()->set('maison.admin', ['name' => '', 'email' => '', 'password' => '']);

    $this->seed(AdminSeeder::class);
})->throws(RuntimeException::class);
