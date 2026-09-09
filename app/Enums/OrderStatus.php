<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasLabel
{
    case New = 'new';
    case Confirmed = 'confirmed';
    case ToCallback = 'to_callback';
    case Prepared = 'prepared';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Returned = 'returned';

    public function getLabel(): string
    {
        return __('enums.order_status.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New => 'info',
            self::Confirmed, self::Prepared, self::Shipped => 'primary',
            self::ToCallback => 'warning',
            self::Delivered => 'success',
            self::Cancelled, self::Returned => 'danger',
        };
    }

    /**
     * Transitions autorisées depuis ce statut.
     * La transition elle-même passe uniquement par Actions\Orders\TransitionOrderStatus.
     *
     * @return list<OrderStatus>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::New => [self::Confirmed, self::ToCallback, self::Cancelled],
            self::ToCallback => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::Prepared, self::Cancelled],
            self::Prepared => [self::Shipped, self::Cancelled],
            self::Shipped => [self::Delivered, self::Returned],
            self::Delivered => [self::Returned],
            self::Cancelled, self::Returned => [],
        };
    }

    public function canTransitionTo(OrderStatus $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function isFinal(): bool
    {
        return $this->allowedTransitions() === [];
    }

    /**
     * Le stock est restitué uniquement à l'annulation.
     */
    public function releasesStock(): bool
    {
        return $this === self::Cancelled;
    }
}
