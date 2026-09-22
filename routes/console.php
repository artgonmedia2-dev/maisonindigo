<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Planificateur
|--------------------------------------------------------------------------
|
| Une seule ligne de cron côté serveur : `php artisan schedule:run` chaque minute.
|
| Sur un hébergement mutualisé (Hostinger), ni Horizon ni un worker permanent
| ne peuvent tourner : la file « database » est vidée chaque minute par un
| worker court, qui s'arrête dès qu'elle est vide. Avec Redis + Horizon (VPS),
| la tâche reste listée mais ne s'exécute pas.
|
*/

Schedule::command('queue:work database --queue=alerts,whatsapp,default --stop-when-empty --max-time=55 --tries=3 --sleep=1')
    ->everyMinute()
    ->when(fn (): bool => config('queue.default') === 'database')
    ->withoutOverlapping(2)
    ->runInBackground();

// Filet de sécurité : une commande dont l'alerte s'est perdue est resignalée.
Schedule::command('mi:alerts-retry')->everyMinute()->withoutOverlapping(2);

// Jobs échoués et lots de plus de trente jours : ménage hebdomadaire.
Schedule::command('queue:prune-failed --hours=720')->weekly()->sundays()->at('04:00');
Schedule::command('queue:prune-batches --hours=720')->weekly()->sundays()->at('04:10');
