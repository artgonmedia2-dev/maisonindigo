<?php

use App\Services\TelegramService;
use Illuminate\Support\Facades\Http;

it('réclame le jeton quand il manque', function () {
    config()->set('maison.telegram.token', null);

    $this->artisan('mi:telegram')
        ->expectsOutputToContain('TELEGRAM_BOT_TOKEN')
        ->assertFailed();
});

it('réclame le destinataire quand seul le jeton est renseigné', function () {
    config()->set('maison.telegram.token', '123:TEST');
    config()->set('maison.telegram.chat_id', null);

    // Le jeton fonctionne peut-être très bien : ne pas accuser Telegram.
    $this->artisan('mi:telegram --test')
        ->expectsOutputToContain('TELEGRAM_CHAT_ID')
        ->assertFailed();
});

it('retrouve l’identifiant de conversation', function () {
    config()->set('maison.telegram.token', '123:TEST');
    Http::fake([TelegramService::API.'/*' => Http::response([
        'ok' => true,
        'result' => [['message' => ['chat' => ['id' => 7768056515, 'first_name' => 'Malon', 'last_name' => 'X', 'type' => 'private']]]],
    ])]);

    $this->artisan('mi:telegram --chat')
        ->expectsOutputToContain('7768056515')
        ->assertSuccessful();
});

it('invite à écrire au robot quand aucune conversation n’existe', function () {
    config()->set('maison.telegram.token', '123:TEST');
    Http::fake([TelegramService::API.'/*' => Http::response(['ok' => true, 'result' => []])]);

    $this->artisan('mi:telegram --chat')
        ->expectsOutputToContain('écrivez un message au robot')
        ->assertSuccessful();
});

it('envoie un message d’essai', function () {
    config()->set('maison.telegram.token', '123:TEST');
    config()->set('maison.telegram.chat_id', '987654');
    Http::fake([TelegramService::API.'/*' => Http::response(['ok' => true])]);

    $this->artisan('mi:telegram --test')
        ->expectsOutputToContain('essai envoyé')
        ->assertSuccessful();

    Http::assertSent(fn ($request): bool => str_contains($request->url(), '/sendMessage'));
});
