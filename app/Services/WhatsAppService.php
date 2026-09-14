<?php

namespace App\Services;

use App\Enums\WhatsAppDirection;
use App\Models\Order;
use App\Models\WhatsAppMessage;
use App\Support\Money;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Meta Cloud API. Toujours appelé depuis un job, jamais dans une requête HTTP.
 * Sans jeton configuré, le message est journalisé et tracé comme « skipped ».
 */
class WhatsAppService
{
    public const API_VERSION = 'v21.0';

    public function isConfigured(): bool
    {
        return filled(config('maison.whatsapp.token')) && filled(config('maison.whatsapp.phone_id'));
    }

    /**
     * Demande de confirmation d'une commande payée à la livraison :
     * « Répondez 1 pour confirmer ».
     */
    public function sendCodConfirmation(Order $order): WhatsAppMessage
    {
        $order->loadMissing('items');

        return $this->sendText($order, $this->codConfirmationText($order));
    }

    public function codConfirmationText(Order $order): string
    {
        /** @var array{name?: string, city?: string} $address */
        $address = $order->shipping_address;

        $lines = $order->items
            ->map(fn ($item): string => "· {$item->title} {$item->size}/{$item->length} × {$item->qty}")
            ->implode("\n");

        return __('storefront.whatsapp.cod_confirmation', [
            'name' => $address['name'] ?? '',
            'number' => $order->number,
            'lines' => $lines,
            'total' => Money::format($order->total),
            'city' => $address['city'] ?? '',
        ]);
    }

    public function sendText(Order $order, string $text): WhatsAppMessage
    {
        /** @var array{phone?: string} $address */
        $address = $order->shipping_address;
        $to = self::normalizePhone($address['phone'] ?? '');

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['preview_url' => false, 'body' => $text],
        ];

        if (! $this->isConfigured()) {
            Log::info('WhatsApp non configuré : message non envoyé.', ['order' => $order->number, 'to' => $to, 'text' => $text]);

            return $this->record($order, $payload, 'skipped');
        }

        $response = Http::withToken((string) config('maison.whatsapp.token'))
            ->acceptJson()
            ->post(sprintf('https://graph.facebook.com/%s/%s/messages', self::API_VERSION, config('maison.whatsapp.phone_id')), $payload);

        if ($response->failed()) {
            Log::warning('WhatsApp : envoi refusé.', ['order' => $order->number, 'status' => $response->status(), 'body' => $response->json()]);

            return $this->record($order, $payload, 'failed');
        }

        /** @var string|null $messageId */
        $messageId = $response->json('messages.0.id');

        return $this->record($order, $payload, 'sent', $messageId);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function record(Order $order, array $payload, string $status, ?string $messageId = null): WhatsAppMessage
    {
        $order->forceFill(['whatsapp_status' => $status])->save();

        return $order->whatsappMessages()->create([
            'direction' => WhatsAppDirection::Outbound,
            'wa_message_id' => $messageId,
            'payload' => $payload,
            'status' => $status,
        ]);
    }

    /**
     * Numéro au format international sans « + » (attendu par l'API) : 06… → 2126…
     */
    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            $digits = '212'.substr($digits, 1);
        }

        return $digits;
    }
}
