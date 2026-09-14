<?php

use App\Models\Admin;
use Filament\Panel;
use Illuminate\Support\Facades\Hash;

it('crée un administrateur depuis la ligne de commande', function () {
    $this->artisan('mi:admin', [
        'email' => 'youssef@maisonindigo.ma',
        'password' => 'MaisonIndigo2026',
        '--name' => 'Youssef',
    ])->assertSuccessful();

    $admin = Admin::query()->where('email', 'youssef@maisonindigo.ma')->first();

    expect($admin)->not->toBeNull()
        ->and($admin?->name)->toBe('Youssef')
        ->and(Hash::check('MaisonIndigo2026', (string) $admin?->password))->toBeTrue()
        ->and($admin?->canAccessPanel(app(Panel::class)))->toBeTrue();
});

it('réinitialise le mot de passe d’un administrateur existant', function () {
    $admin = Admin::factory()->create(['email' => 'youssef@maisonindigo.ma']);

    $this->artisan('mi:admin', [
        'email' => 'youssef@maisonindigo.ma',
        'password' => 'NouveauMotDePasse2026',
    ])->assertSuccessful();

    expect(Admin::query()->count())->toBe(1)
        ->and(Hash::check('NouveauMotDePasse2026', (string) $admin->refresh()->password))->toBeTrue();
});

it('refuse une adresse invalide ou un mot de passe trop court', function () {
    $this->artisan('mi:admin', ['email' => 'pas-une-adresse', 'password' => 'court'])
        ->assertFailed();

    expect(Admin::query()->count())->toBe(0);
});
