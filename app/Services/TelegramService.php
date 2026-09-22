<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Support\Money;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Alerte instantanée de la maison sur Telegram : une nouvelle commande arrive.
 *
 * Contrairement à WhatsApp, ce canal ne s'adresse jamais au client. Sans jeton
 * configuré, rien n'est envoyé et l'appel est simplement journalisé : la
 * boutique doit continuer d'encaisser même si la messagerie tombe.
 */
class TelegramService
{
    public const API = 'https://api.telegram.org';

    public function isConfigured(): bool
    {
        return filled(config('maison.telegram.token')) && filled(config('maison.telegram.chat_id'));
    }

    /**
     * Prévient la maison d'une commande. Renvoie vrai si Telegram a accusé
     * réception : l'appelant s'en sert pour savoir s'il faut réessayer.
     */
    public function sendOrderAlert(Order $order): bool
    {
        $order->loadMissing('items');

        return $this->send($this->orderText($order));
    }

    public function sendTest(): bool
    {
        return $this->send(__('storefront.alerts.order_test'));
    }

    public function orderText(Order $order): string
    {
        /** @var array{name?: string, phone?: string, city?: string} $address */
        $address = $order->shipping_address;

        $lines = $order->items
            ->map(fn (OrderItem $item): string => '· '.$this->escape("{$item->title} {$item->size}/{$item->length} × {$item->qty}"))
            ->implode("\n");

        $titre = $this->escape(__('storefront.alerts.order_title', ['number' => $order->number]));
        $client = $this->escape(trim(($address['name'] ?? '').' · '.($address['city'] ?? ''), ' ·'));
        $telephone = $this->escape((string) ($address['phone'] ?? ''));
        $total = $this->escape(Money::format($order->total));
        $paiement = $this->escape($order->payment_method->getLabel());
        $lien = $this->escape(rtrim((string) config('app.url'), '/')."/admin/orders/{$order->id}");
        $ouvrir = $this->escape(__('storefront.alerts.order_open'));

        return "<b>{$titre}</b>\n\n{$client}\n{$telephone}\n\n{$lines}\n\n"
            ."<b>{$total}</b> · {$paiement}\n\n"
            ."<a href=\"{$lien}\">{$ouvrir}</a>";
    }

    private function send(string $text): bool
    {
        if (! $this->isConfigured()) {
            Log::info('Alerte Telegram ignorée : jeton ou destinataire absent.');

            return false;
        }

        $token = (string) config('maison.telegram.token');

        $response = Http::timeout(8)
            ->retry(2, 200, throw: false)
            ->asJson()
            ->post(self::API."/bot{$token}/sendMessage", [
                'chat_id' => config('maison.telegram.chat_id'),
                'text' => $text,
                'parse_mode' => 'HTML',
                // L'aperçu du lien vers le back-office n'apporte rien.
                'link_preview_options' => ['is_disabled' => true],
            ]);

        if ($response->failed()) {
            Log::warning('Alerte Telegram refusée.', ['status' => $response->status(), 'body' => $response->body()]);

            return false;
        }

        return true;
    }

    /** Telegram interprète le HTML : tout ce qui vient du client est échappé. */
    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
