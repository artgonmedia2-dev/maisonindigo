<?php

namespace App\Data;

use App\Models\Discount;

/**
 * Remise appliquée à un panier : montant en centimes, libellé, code éventuel.
 */
final readonly class DiscountResult
{
    public function __construct(
        public int $total,
        public ?string $label = null,
        public ?string $code = null,
        public ?Discount $discount = null,
        /** Le code saisi n'a pas pu être appliqué. */
        public ?string $codeError = null,
    ) {}

    public static function none(?string $codeError = null): self
    {
        return new self(total: 0, codeError: $codeError);
    }

    public function isApplied(): bool
    {
        return $this->total > 0;
    }

    /**
     * @return array{total: int, label: string|null, code: string|null, code_error: string|null}
     */
    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'label' => $this->label,
            'code' => $this->code,
            'code_error' => $this->codeError,
        ];
    }
}
