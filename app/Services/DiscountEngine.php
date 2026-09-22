<?php

namespace App\Services;

use App\Data\DiscountResult;
use App\Enums\DiscountType;
use App\Models\Discount;
use App\Support\Money;

/**
 * Calcule la remise d'un panier : remise automatique « lot » (2 jeans -10 %)
 * ou code saisi (pourcentage, montant fixe). La plus avantageuse s'applique ;
 * les remises ne se cumulent pas.
 */
class DiscountEngine
{
    public function compute(int $subtotal, int $quantity, ?string $code = null): DiscountResult
    {
        if ($subtotal <= 0 || $quantity <= 0) {
            return DiscountResult::none();
        }

        $automatic = $this->bestAutomatic($subtotal, $quantity);
        $codeError = null;

        if ($code !== null && trim($code) !== '') {
            $fromCode = $this->fromCode($subtotal, $quantity, trim($code));

            if ($fromCode === null) {
                $codeError = __('storefront.cart.code_invalid');
            } elseif ($automatic === null || $fromCode->total >= $automatic->total) {
                return $fromCode;
            }
        }

        if ($automatic === null) {
            return DiscountResult::none($codeError);
        }

        return new DiscountResult(
            total: $automatic->total,
            label: $automatic->label,
            code: null,
            discount: $automatic->discount,
            codeError: $codeError,
        );
    }

    /**
     * L'offre « lot » telle qu'elle s'appliquera au panier, pour un prix
     * unitaire donné.
     *
     * La fiche produit l'affiche au lieu de recalculer un barrème à part :
     * le prix annoncé est celui que le panier facturera, toujours.
     *
     * @return array{quantity: int, subtotal: int, total: int, unit: int, label: string}|null
     */
    public function packOffer(int $unitPrice): ?array
    {
        if ($unitPrice <= 0) {
            return null;
        }

        $best = null;

        $bundles = Discount::query()
            ->active()
            ->whereNull('code')
            ->where('type', DiscountType::Bundle)
            ->get();

        foreach ($bundles as $discount) {
            /** @var array<string, mixed> $rules */
            $rules = $discount->rules ?? [];
            $quantity = max(2, (int) ($rules['min_qty'] ?? 2));
            $subtotal = $unitPrice * $quantity;

            $result = $this->apply($discount, $subtotal, $quantity);

            if ($result === null) {
                continue;
            }

            $total = $subtotal - $result->total;

            if ($best === null || $total < $best['total']) {
                $best = [
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                    'total' => $total,
                    // Le prix à l'unité, arrondi vers le bas : on n'annonce
                    // jamais moins cher que ce qui sera facturé.
                    'unit' => intdiv($total, $quantity),
                    'label' => $discount->name,
                ];
            }
        }

        return $best;
    }

    private function bestAutomatic(int $subtotal, int $quantity): ?DiscountResult
    {
        $best = null;

        $automatics = Discount::query()
            ->active()
            ->whereNull('code')
            ->get();

        foreach ($automatics as $discount) {
            $result = $this->apply($discount, $subtotal, $quantity);

            if ($result !== null && ($best === null || $result->total > $best->total)) {
                $best = $result;
            }
        }

        return $best;
    }

    private function fromCode(int $subtotal, int $quantity, string $code): ?DiscountResult
    {
        $discount = Discount::query()
            ->active()
            ->whereRaw('UPPER(code) = ?', [mb_strtoupper($code)])
            ->first();

        if ($discount === null) {
            return null;
        }

        return $this->apply($discount, $subtotal, $quantity);
    }

    private function apply(Discount $discount, int $subtotal, int $quantity): ?DiscountResult
    {
        if (! $discount->isCurrent()) {
            return null;
        }

        /** @var array<string, mixed> $rules */
        $rules = $discount->rules ?? [];
        $minQty = (int) ($rules['min_qty'] ?? 1);
        $minSubtotal = (int) ($rules['min_subtotal'] ?? 0);

        if ($quantity < $minQty || $subtotal < $minSubtotal) {
            return null;
        }

        $total = match ($discount->type) {
            DiscountType::Percent, DiscountType::Bundle => Money::percentOf($subtotal, $discount->value),
            DiscountType::Fixed => min($discount->value, $subtotal),
        };

        if ($total <= 0) {
            return null;
        }

        return new DiscountResult(
            total: $total,
            label: $discount->name,
            code: $discount->code,
            discount: $discount,
        );
    }
}
