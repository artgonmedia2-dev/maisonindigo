<?php

namespace App\Console\Commands;

use App\Jobs\SendOrderAlert;
use App\Models\Order;
use Illuminate\Console\Command;

/**
 * Rattrape les commandes dont l'alerte ne s'est jamais envoyée.
 *
 * L'alerte part normalement juste après la réponse HTTP. Un processus PHP
 * interrompu, une coupure réseau ou une panne de Telegram la font disparaître
 * sans laisser de trace dans la file. Cette tâche, lancée chaque minute,
 * repasse derrière : une commande n'est jamais perdue de vue.
 */
class RetryOrderAlertsCommand extends Command
{
    protected $signature = 'mi:alerts-retry
        {--minutes=2 : Âge minimal d’une commande avant de la resignaler}
        {--limit=20 : Nombre de commandes traitées par passage}';

    protected $description = 'Resignale les commandes dont l’alerte ne s’est pas envoyée';

    public function handle(): int
    {
        $orders = Order::query()
            ->whereNull('alerted_at')
            ->where('created_at', '<=', now()->subMinutes((int) $this->option('minutes')))
            ->orderBy('created_at')
            ->limit((int) $this->option('limit'))
            ->get();

        if ($orders->isEmpty()) {
            $this->components->info('Chaque commande a bien été signalée.');

            return self::SUCCESS;
        }

        $orders->each(fn (Order $order) => SendOrderAlert::dispatch($order));

        $this->components->warn($orders->count().' commande(s) remise(s) en file.');

        return self::SUCCESS;
    }
}
