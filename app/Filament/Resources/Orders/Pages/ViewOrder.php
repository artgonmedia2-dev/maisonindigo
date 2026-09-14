<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\OrderStatus;
use App\Exceptions\InvalidStatusTransitionException;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Admin;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        /** @var Order $order */
        $order = $this->getRecord();

        return $order->number;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('transition')
                ->label(__('admin.orders.change_status'))
                ->icon('heroicon-o-arrow-right-circle')
                ->visible(fn (): bool => $this->allowedTransitions() !== [])
                ->schema([
                    Select::make('status')
                        ->label(__('admin.orders.new_status'))
                        ->options(fn (): array => $this->allowedTransitions())
                        ->required()
                        ->native(false),
                    Textarea::make('comment')
                        ->label(__('admin.orders.comment'))
                        ->rows(3)
                        ->maxLength(500),
                ])
                ->action(function (array $data, TransitionOrderStatus $transition): void {
                    /** @var Order $order */
                    $order = $this->getRecord();
                    /** @var Admin|null $admin */
                    $admin = Filament::auth()->user();

                    try {
                        $transition->handle($order, OrderStatus::from((string) $data['status']), $admin, isset($data['comment']) ? (string) $data['comment'] : null);
                    } catch (InvalidStatusTransitionException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();

                        return;
                    }

                    Notification::make()->title(__('admin.orders.status_changed'))->success()->send();
                    $this->refreshFormData(['status', 'confirmed_at', 'shipped_at', 'delivered_at']);
                }),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function allowedTransitions(): array
    {
        /** @var Order $order */
        $order = $this->getRecord();
        $options = [];

        foreach ($order->status->allowedTransitions() as $status) {
            $options[$status->value] = $status->getLabel();
        }

        return $options;
    }
}
