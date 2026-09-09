<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderSequence;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = 49900;
        $shipping = 3500;

        return [
            'number' => OrderSequence::formatNumber((int) date('Y'), fake()->unique()->numberBetween(1, 999999)),
            'customer_id' => Customer::factory(),
            'status' => OrderStatus::New,
            'payment_method' => PaymentMethod::Cod,
            'subtotal' => $subtotal,
            'discount_total' => 0,
            'shipping_total' => $shipping,
            'total' => $subtotal + $shipping,
            'currency' => 'MAD',
            'shipping_address' => [
                'name' => fake()->name(),
                'phone' => '+2126'.fake()->numerify('########'),
                'line1' => fake()->streetAddress(),
                'line2' => null,
                'city' => 'Casablanca',
                'region' => null,
                'zone' => 'Casablanca',
            ],
            'discount_code' => null,
            'customer_notes' => null,
            'notes' => null,
            'confirmed_at' => null,
            'shipped_at' => null,
            'delivered_at' => null,
            'whatsapp_status' => null,
        ];
    }

    public function transfer(): static
    {
        return $this->state(fn (array $attributes) => ['payment_method' => PaymentMethod::Transfer]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Confirmed,
            'confirmed_at' => now(),
        ]);
    }

    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Shipped,
            'confirmed_at' => now()->subDay(),
            'shipped_at' => now(),
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Delivered,
            'confirmed_at' => now()->subDays(3),
            'shipped_at' => now()->subDays(2),
            'delivered_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => ['status' => OrderStatus::Cancelled]);
    }
}
