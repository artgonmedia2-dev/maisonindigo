<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Envoi WhatsApp en arrière-plan. Type « cod_confirmation » à la création d'une commande COD.
 */
class SendWhatsAppMessage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 120, 600];

    public function __construct(
        public Order $order,
        public string $type = 'cod_confirmation',
    ) {
        $this->onQueue('whatsapp');
    }

    public function handle(WhatsAppService $whatsApp): void
    {
        match ($this->type) {
            'cod_confirmation' => $whatsApp->sendCodConfirmation($this->order),
            default => null,
        };
    }
}
