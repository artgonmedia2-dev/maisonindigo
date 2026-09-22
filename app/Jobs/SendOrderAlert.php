<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Prévient la maison d'une nouvelle commande.
 *
 * Lancé juste après la réponse HTTP du tunnel de commande : le client n'attend
 * pas l'appel réseau, et l'alerte ne dépend pas du passage du cron. La date
 * d'envoi est inscrite sur la commande, ce qui permet à la tâche de rattrapage
 * de repérer celles qui n'ont jamais été signalées.
 */
class SendOrderAlert implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [10, 60, 300];

    public function __construct(public Order $order)
    {
        $this->onQueue('alerts');
    }

    public function handle(TelegramService $telegram): void
    {
        if ($this->order->alerted_at !== null) {
            return;
        }

        if ($telegram->sendOrderAlert($this->order)) {
            $this->order->forceFill(['alerted_at' => now()])->save();
        }
    }
}
