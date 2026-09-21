<?php

namespace App\Data;

use App\Enums\PaymentMethod;

/**
 * Données validées du formulaire de commande : nom, mobile, ville, adresse.
 */
final readonly class CheckoutData
{
    public function __construct(
        public string $name,
        public string $phone,
        public string $city,
        public string $line1,
        public PaymentMethod $paymentMethod,
        public ?string $discountCode = null,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            name: (string) $validated['name'],
            phone: (string) $validated['phone'],
            city: (string) $validated['city'],
            line1: (string) $validated['line1'],
            paymentMethod: PaymentMethod::from((string) $validated['payment_method']),
            discountCode: isset($validated['discount_code']) && $validated['discount_code'] !== ''
                ? (string) $validated['discount_code']
                : null,
        );
    }

    /**
     * Adresse figée sur la commande. La zone vient du calcul de livraison.
     *
     * @return array{name: string, phone: string, city: string, line1: string, zone: string}
     */
    public function toSnapshot(string $zoneName): array
    {
        return [
            'name' => $this->name,
            'phone' => $this->phone,
            'city' => $this->city,
            'line1' => $this->line1,
            'zone' => $zoneName,
        ];
    }
}
