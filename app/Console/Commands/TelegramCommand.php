<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Met en service l'alerte instantanée des commandes.
 *
 *   php artisan mi:telegram          état de la configuration
 *   php artisan mi:telegram --chat   retrouve l'identifiant de conversation
 *   php artisan mi:telegram --test   envoie un message d'essai
 */
class TelegramCommand extends Command
{
    protected $signature = 'mi:telegram
        {--chat : Affiche les conversations qui ont écrit au robot}
        {--test : Envoie un message d’essai}';

    protected $description = 'Vérifie et met en service l’alerte Telegram des nouvelles commandes';

    public function handle(TelegramService $telegram): int
    {
        $token = (string) config('maison.telegram.token');
        $chat = (string) config('maison.telegram.chat_id');

        $this->components->twoColumnDetail('Jeton du robot', $token === '' ? '<fg=red>absent</>' : '<fg=green>renseigné</>');
        $this->components->twoColumnDetail('Destinataire', $chat === '' ? '<fg=red>absent</>' : "<fg=green>{$chat}</>");

        if ($token === '') {
            $this->components->error(
                'Renseignez TELEGRAM_BOT_TOKEN dans .env. '
                .'Le jeton est donné par @BotFather sur Telegram, à la création du robot.'
            );

            return self::FAILURE;
        }

        if ($this->option('chat')) {
            return $this->listChats($token);
        }

        if ($this->option('test')) {
            if (! $telegram->sendTest()) {
                $this->components->error('Telegram a refusé le message. Voir php artisan mi:logs.');

                return self::FAILURE;
            }

            $this->components->info('Message d’essai envoyé. Regardez votre téléphone.');
        }

        return self::SUCCESS;
    }

    /**
     * Telegram ne révèle l'identifiant d'une conversation qu'après un premier
     * message : écrivez « bonjour » au robot, puis relancez cette commande.
     */
    private function listChats(string $token): int
    {
        $response = Http::timeout(10)->get(TelegramService::API."/bot{$token}/getUpdates");

        if ($response->failed()) {
            $this->components->error('Telegram a refusé la demande : '.$response->body());

            return self::FAILURE;
        }

        /** @var array<int, array<string, mixed>> $updates */
        $updates = $response->json('result', []);

        $chats = collect($updates)
            ->map(fn (array $update): ?array => $update['message']['chat'] ?? $update['channel_post']['chat'] ?? null)
            ->filter()
            ->unique('id');

        if ($chats->isEmpty()) {
            $this->components->warn(
                'Aucune conversation. Ouvrez Telegram, écrivez un message au robot, puis relancez cette commande.'
            );

            return self::SUCCESS;
        }

        $this->newLine();
        $this->components->info('À reporter dans TELEGRAM_CHAT_ID :');

        foreach ($chats as $chatInfo) {
            $nom = $chatInfo['title'] ?? trim(($chatInfo['first_name'] ?? '').' '.($chatInfo['last_name'] ?? ''));
            $this->components->twoColumnDetail((string) ($nom ?: $chatInfo['type'] ?? 'conversation'), "<fg=green>{$chatInfo['id']}</>");
        }

        return self::SUCCESS;
    }
}
