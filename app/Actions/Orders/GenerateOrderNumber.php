<?php

namespace App\Actions\Orders;

use App\Models\OrderSequence;
use Illuminate\Support\Facades\DB;

/**
 * Numéro de commande MI-{AAAA}-{NNNNNN}, séquentiel par année.
 * À appeler dans la transaction qui crée la commande : la ligne de séquence est verrouillée.
 */
class GenerateOrderNumber
{
    public function handle(?int $year = null): string
    {
        $year ??= (int) now()->format('Y');

        return DB::transaction(function () use ($year): string {
            /** @var OrderSequence|null $sequence */
            $sequence = OrderSequence::query()->where('year', $year)->lockForUpdate()->first();

            if ($sequence === null) {
                $sequence = OrderSequence::query()->create(['year' => $year, 'last_number' => 0]);
            }

            $next = $sequence->last_number + 1;
            $sequence->forceFill(['last_number' => $next])->save();

            return OrderSequence::formatNumber($year, $next);
        });
    }
}
