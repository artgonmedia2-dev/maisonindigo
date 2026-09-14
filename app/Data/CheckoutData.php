<?php

namespace App\Data;

use App\Enums\PaymentMethod;

/**
 * Données validées du formulaire de commande.
 */
final readonly class CheckoutData
{
    public function __construct(
        public string $name,
        public string $phone,
        public ?string $email,
        public string $line1,
        public ?string $line2,
        public string $city,
        public ?string $region,
        public PaymentMethod $paymentMethod,
        public ?string $discountCode = null,
        public ?string $notes = null,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            name: (string) $validated['name'],
            phone: (string) $validated['phone'],
            email: isset($validated['email']) && $validated['email'] !== '' ? (string) $validated['email'] : null,
            line1: (string) $validated['line1'],
            line2: isset($validated['line2']) && $validated['line2'] !== '' ? (string) $validated['line2'] : null,
            city: (string) $validated['city'],
            region: isset($validated['region']) && $validated['region'] !== '' ? (string) $validated['region'] : null,
            paymentMethod: PaymentMethod::from((string) $validated['payment_method']),
            discountCode: isset($validated['discount_code']) && $validated['discount_code'] !== '' ? (string) $validated['discount_code'] : null,
            notes: isset($validated['notes']) && $validated['notes'] !== '' ? (string) $validated['notes'] : null,
        );
    }

    /**
     * Adresse figée sur la commande.
     *
     * @return array{name: string, phone: string, email: string|null, line1: string, line2: string|null, city: string, region: string|null, zone: string}
     */
    public function toSnapshot(string $zoneName): array
    {
        return [
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'line1' => $this->line1,
            'line2' => $this->line2,
            'city' => $this->city,
            'region' => $this->region,
            'zone' => $zoneName,
        ];
    }
}
