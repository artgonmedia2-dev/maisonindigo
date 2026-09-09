<?php

namespace Database\Factories;

use App\Enums\WhatsAppDirection;
use App\Models\Order;
use App\Models\WhatsAppMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WhatsAppMessage>
 */
class WhatsAppMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'direction' => WhatsAppDirection::Outbound,
            'wa_message_id' => 'wamid.'.fake()->sha1(),
            'payload' => ['type' => 'template', 'name' => 'order_confirmation'],
            'status' => 'sent',
        ];
    }

    public function inbound(): static
    {
        return $this->state(fn (array $attributes) => [
            'direction' => WhatsAppDirection::Inbound,
            'payload' => ['type' => 'text', 'text' => ['body' => '1']],
            'status' => null,
        ]);
    }
}
