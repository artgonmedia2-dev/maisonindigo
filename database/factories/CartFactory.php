<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Cart>
 */
class CartFactory extends Factory
{
    public function definition(): array
    {
        return [
            'session_id' => Str::random(40),
            'customer_id' => null,
        ];
    }

    public function forCustomer(?Customer $customer = null): static
    {
        return $this->state(fn (array $attributes) => [
            'session_id' => null,
            'customer_id' => $customer ?? Customer::factory(),
        ]);
    }
}
