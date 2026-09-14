<?php

namespace App\Services;

use App\Data\ShippingQuote;
use App\Models\ShippingZone;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

/**
 * Détermine la zone d'une ville et le coût de livraison pour un sous-total.
 * Les zones sont mises en cache ; `forget()` est appelé à leur modification.
 */
class ShippingCalculator
{
    public const CACHE_KEY = 'shipping.zones';

    /** @var Collection<int, ShippingZone>|null */
    private ?Collection $zones = null;

    /**
     * @return Collection<int, ShippingZone>
     */
    public function zones(): Collection
    {
        $this->zones ??= Cache::remember(
            self::CACHE_KEY,
            now()->addHours(6),
            fn (): Collection => ShippingZone::query()
                ->with('rate')
                ->where('is_active', true)
                ->orderBy('position')
                ->get(),
        );

        return $this->zones;
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Zone desservant la ville, sinon la zone de repli (sans villes listées).
     */
    public function zoneForCity(string $city): ShippingZone
    {
        $zones = $this->zones();

        foreach ($zones as $zone) {
            if ($zone->servesCity($city)) {
                return $zone;
            }
        }

        $fallback = $zones->first(fn (ShippingZone $zone): bool => $zone->cities === []);

        if ($fallback === null) {
            throw new RuntimeException('Aucune zone de livraison de repli : ajoutez une zone sans villes.');
        }

        return $fallback;
    }

    public function quote(string $city, int $subtotal): ShippingQuote
    {
        $zone = $this->zoneForCity($city);
        $rate = $zone->rate;

        if ($rate === null) {
            throw new RuntimeException("La zone « {$zone->name} » n'a pas de tarif.");
        }

        return new ShippingQuote(
            zoneId: $zone->id,
            zoneName: $zone->name,
            delay: $zone->delay,
            cost: $rate->costFor($subtotal),
            price: $rate->price,
            freeThreshold: $rate->free_threshold,
        );
    }

    /**
     * Seuil de livraison offerte le plus courant (affiché dans le panier).
     */
    public function freeThreshold(): ?int
    {
        $thresholds = $this->zones()
            ->map(fn (ShippingZone $zone): ?int => $zone->rate?->free_threshold)
            ->filter()
            ->countBy()
            ->sortDesc();

        $threshold = $thresholds->keys()->first();

        return $threshold === null ? null : (int) $threshold;
    }

    /**
     * Toutes les villes connues, triées, pour l'aide à la saisie.
     *
     * @return list<string>
     */
    public function cities(): array
    {
        /** @var list<string> $cities */
        $cities = $this->zones()
            ->flatMap(fn (ShippingZone $zone): array => $zone->cities)
            ->map(fn ($city): string => (string) $city)
            ->unique()
            ->sort(fn (string $a, string $b): int => strcoll($a, $b))
            ->values()
            ->all();

        return $cities;
    }
}
