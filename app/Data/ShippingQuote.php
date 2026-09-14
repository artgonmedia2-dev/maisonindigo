<?php

namespace App\Data;

/**
 * Résultat du calcul de livraison pour une ville et un sous-total.
 */
final readonly class ShippingQuote
{
    public function __construct(
        public int $zoneId,
        public string $zoneName,
        public string $delay,
        /** Coût facturé, en centimes (0 si offerte). */
        public int $cost,
        /** Tarif de la zone hors seuil, en centimes. */
        public int $price,
        public ?int $freeThreshold,
    ) {}

    public function isFree(): bool
    {
        return $this->cost === 0;
    }

    /**
     * @return array{zone_id: int, zone_name: string, delay: string, cost: int, price: int, free_threshold: int|null, is_free: bool}
     */
    public function toArray(): array
    {
        return [
            'zone_id' => $this->zoneId,
            'zone_name' => $this->zoneName,
            'delay' => $this->delay,
            'cost' => $this->cost,
            'price' => $this->price,
            'free_threshold' => $this->freeThreshold,
            'is_free' => $this->isFree(),
        ];
    }
}
