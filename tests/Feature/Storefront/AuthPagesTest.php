<?php

use App\Models\Customer;
use Inertia\Testing\AssertableInertia as Assert;

it('rend les pages d’authentification dans le gabarit de la maison', function (string $url, string $component) {
    $this->get($url)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component($component)
            ->has('maison.name')
            ->has('maison.contact.email')
        );
})->with([
    'connexion' => ['/login', 'Auth/Login'],
    'inscription' => ['/register', 'Auth/Register'],
    'mot de passe oublié' => ['/forgot-password', 'Auth/ForgotPassword'],
]);

it('propose la réinitialisation du mot de passe depuis la connexion', function () {
    $this->get('/login')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/Login')
            ->where('canResetPassword', true)
        );
});

it('ouvre l’espace client à un client connecté', function () {
    $customer = Customer::factory()->create(['name' => 'Salma Idrissi']);

    $this->actingAs($customer)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('auth.user.name', 'Salma Idrissi')
        );
});

it('affiche la page des informations du client', function () {
    $customer = Customer::factory()->create();

    $this->actingAs($customer)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Profile/Edit')
            ->has('mustVerifyEmail')
        );
});

it('renvoie un visiteur vers la connexion pour l’espace client', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});
