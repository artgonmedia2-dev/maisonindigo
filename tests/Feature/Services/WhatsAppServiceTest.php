<?php

use App\Enums\WhatsAppDirection;
use App\Jobs\SendWhatsAppMessage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Http;

it('normalise les numéros marocains au format international', function () {
    expect(WhatsAppService::normalizePhone('06 12 34 56 78'))->toBe('212612345678')
        ->and(WhatsAppService::normalizePhone('+212 6 12 34 56 78'))->toBe('212612345678')
        ->and(WhatsAppService::normalizePhone('00212612345678'))->toBe('212612345678');
});

it('rédige la demande de confirmation dans la voix de la maison', function () {
    $order = Order::factory()->create(['number' => 'MI-2026-000042', 'total' => 53400]);
    $item = OrderItem::factory()->for($order)->create(['size' => 32, 'length' => 32, 'qty' => 1]);

    $text = app(WhatsAppService::class)->codConfirmationText($order->load('items'));

    expect($text)->toContain('MI-2026-000042')
        ->toContain("{$item->title} {$item->size}/{$item->length} × 1")
        ->toContain('Répondez 1 pour confirmer')
        ->not->toContain('!');
});

it('trace le message comme ignoré sans jeton configuré', function () {
    config()->set('maison.whatsapp.token', null);
    $order = Order::factory()->create();
    OrderItem::factory()->for($order)->create();

    $message = app(WhatsAppService::class)->sendCodConfirmation($order);

    expect($message->status)->toBe('skipped')
        ->and($message->direction)->toBe(WhatsAppDirection::Outbound)
        ->and($order->refresh()->whatsapp_status)->toBe('skipped');
});

it('envoie via Meta Cloud API quand le jeton est configuré', function () {
    config()->set('maison.whatsapp.token', 'jeton-test');
    config()->set('maison.whatsapp.phone_id', '123456');
    Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.ABC']]], 200)]);

    $order = Order::factory()->create();
    OrderItem::factory()->for($order)->create();

    (new SendWhatsAppMessage($order))->handle(app(WhatsAppService::class));

    Http::assertSent(fn ($request) => str_contains($request->url(), '/123456/messages')
        && $request['to'] === '212'.substr(preg_replace('/\D+/', '', $order->shipping_address['phone']), 3)
        && $request['type'] === 'text');

    expect($order->refresh()->whatsapp_status)->toBe('sent')
        ->and($order->whatsappMessages()->firstOrFail()->wa_message_id)->toBe('wamid.ABC');
});

it('trace un échec d’envoi', function () {
    config()->set('maison.whatsapp.token', 'jeton-test');
    config()->set('maison.whatsapp.phone_id', '123456');
    Http::fake(['graph.facebook.com/*' => Http::response(['error' => 'invalid'], 400)]);

    $order = Order::factory()->create();
    OrderItem::factory()->for($order)->create();

    app(WhatsAppService::class)->sendCodConfirmation($order);

    expect($order->refresh()->whatsapp_status)->toBe('failed');
});
