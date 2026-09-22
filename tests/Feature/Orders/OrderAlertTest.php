<?php

use App\Console\Commands\RetryOrderAlertsCommand;
use App\Jobs\SendOrderAlert;
use App\Models\Order;
use App\Services\TelegramService;
use App\Support\Money;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config()->set('maison.telegram.token', '123:TEST');
    config()->set('maison.telegram.chat_id', '987654');
    config()->set('app.url', 'https://maisonindigo.shop');
});

it('compose une alerte lisible avec le lien vers la commande', function () {
    $order = Order::factory()
        ->hasItems(1, ['qty' => 2])
        ->create([
            'number' => 'MI-2026-000412',
            'total' => 94800,
            'shipping_address' => ['name' => 'Salma Idrissi', 'phone' => '0612345678', 'city' => 'Casablanca'],
        ]);

    $texte = app(TelegramService::class)->orderText($order);
    $ligne = $order->items->first();

    expect($texte)->toContain('MI-2026-000412')
        ->and($texte)->toContain('Salma Idrissi · Casablanca')
        ->and($texte)->toContain('0612345678')
        ->and($texte)->toContain("{$ligne->title} {$ligne->size}/{$ligne->length} × 2")
        ->and($texte)->toContain(Money::format($order->total))
        ->and($texte)->toContain("https://maisonindigo.shop/admin/orders/{$order->id}");
});

it('échappe ce que le client a saisi', function () {
    $order = Order::factory()->create([
        'shipping_address' => ['name' => '<b>Salma</b>', 'phone' => '0612345678', 'city' => 'Casa & Mers'],
    ]);

    $texte = app(TelegramService::class)->orderText($order);

    // Telegram interprète le HTML : une balise saisie casserait le message.
    expect($texte)->toContain('&lt;b&gt;Salma&lt;/b&gt;')
        ->and($texte)->toContain('Casa &amp; Mers');
});

it('envoie l’alerte et marque la commande', function () {
    Http::fake([TelegramService::API.'/*' => Http::response(['ok' => true])]);

    $order = Order::factory()->create(['alerted_at' => null]);

    app(SendOrderAlert::class, ['order' => $order])->handle(app(TelegramService::class));

    Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/bot123:TEST/sendMessage')
        && $request['chat_id'] === '987654'
        && $request['parse_mode'] === 'HTML');

    expect($order->fresh()->alerted_at)->not->toBeNull();
});

it('laisse la commande à resignaler quand Telegram refuse', function () {
    Http::fake([TelegramService::API.'/*' => Http::response(['ok' => false], 500)]);

    $order = Order::factory()->create(['alerted_at' => null]);

    app(SendOrderAlert::class, ['order' => $order])->handle(app(TelegramService::class));

    expect($order->fresh()->alerted_at)->toBeNull();
});

it('n’envoie rien sans configuration', function () {
    Http::fake();
    config()->set('maison.telegram.token', null);

    $order = Order::factory()->create(['alerted_at' => null]);

    app(SendOrderAlert::class, ['order' => $order])->handle(app(TelegramService::class));

    Http::assertNothingSent();
    expect($order->fresh()->alerted_at)->toBeNull();
});

it('ne signale pas deux fois la même commande', function () {
    Http::fake([TelegramService::API.'/*' => Http::response(['ok' => true])]);

    $order = Order::factory()->create(['alerted_at' => now()]);

    app(SendOrderAlert::class, ['order' => $order])->handle(app(TelegramService::class));

    Http::assertNothingSent();
});

it('rattrape une commande dont l’alerte s’est perdue', function () {
    Queue::fake();

    $perdue = Order::factory()->create(['alerted_at' => null, 'created_at' => now()->subMinutes(10)]);
    Order::factory()->create(['alerted_at' => now(), 'created_at' => now()->subMinutes(10)]);
    // Trop récente : l'envoi après réponse est peut-être encore en cours.
    Order::factory()->create(['alerted_at' => null, 'created_at' => now()]);

    $this->artisan(RetryOrderAlertsCommand::class)->assertSuccessful();

    Queue::assertPushed(SendOrderAlert::class, 1);
    Queue::assertPushed(
        fn (SendOrderAlert $job): bool => $job->order->is($perdue)
    );
});
