<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Admin;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

/**
 * Seul point de changement de statut : valide la transition, horodate,
 * restitue le stock à l'annulation et écrit l'historique.
 */
class TransitionOrderStatus
{
    public function handle(Order $order, OrderStatus $to, ?Admin $by = null, ?string $comment = null): Order
    {
        $from = $order->status;

        if (! $from->canTransitionTo($to)) {
            throw new InvalidStatusTransitionException($from, $to);
        }

        return DB::transaction(function () use ($order, $from, $to, $by, $comment): Order {
            $attributes = ['status' => $to];

            match ($to) {
                OrderStatus::Confirmed => $attributes['confirmed_at'] = now(),
                OrderStatus::Shipped => $attributes['shipped_at'] = now(),
                OrderStatus::Delivered => $attributes['delivered_at'] = now(),
                default => null,
            };

            $order->forceFill($attributes)->save();

            if ($to->releasesStock()) {
                $this->releaseStock($order);
            }

            $order->statusHistories()->create([
                'from_status' => $from,
                'to_status' => $to,
                'admin_id' => $by?->id,
                'comment' => $comment,
            ]);

            return $order->refresh();
        });
    }

    private function releaseStock(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            if ($item->product_variant_id === null) {
                continue;
            }

            ProductVariant::query()
                ->whereKey($item->product_variant_id)
                ->increment('stock', $item->qty);
        }
    }
}
