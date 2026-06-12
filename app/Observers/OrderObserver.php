<?php

declare(strict_types=1);

namespace App\Observers;

use App\Dto\Order\OrderStatusChangedDto;
use App\Enums\OrderStatusEnum;
use App\Events\OrderCreatedEvent;
use App\Events\OrderStatusChangedEvent;
use App\Models\Order;

use function is_int;
use function is_string;

final class OrderObserver
{
    public function created(Order $model): void
    {
        $user = $model->user;

        if ($user !== null) {
            OrderCreatedEvent::dispatch(
                new OrderStatusChangedDto(
                    orderId: $model->id,
                    oldStatus: '',
                    newStatus: $model->status,
                    userEmail: $user->email ?? '',
                    userName: $user->name ?? '',
                    total: $model->total,
                ),
            );
        }
    }

    public function updated(Order $model): void
    {
        if ($model->wasRecentlyCreated) {
            return;
        }

        $statusChanged = $model->wasChanged('status');
        $paymentChanged = $model->wasChanged('payment_method');

        if (!$statusChanged && !$paymentChanged) {
            return;
        }

        $rawStatus = $model->getAttributes()['status'] ?? null;
        $newStatus = is_int($rawStatus) || is_string($rawStatus)
            ? OrderStatusEnum::tryFrom($rawStatus)
            : null;

        if (!$paymentChanged && ($newStatus === null || !$newStatus->shouldNotify())) {
            return;
        }

        $user = $model->user;
        $originalRaw = $model->getOriginal('status');
        $originalStatus = is_string($originalRaw) || is_int($originalRaw)
            ? (string) $originalRaw
            : '';

        if ($user !== null) {
            OrderStatusChangedEvent::dispatch(
                new OrderStatusChangedDto(
                    orderId: $model->id,
                    oldStatus: $originalStatus,
                    newStatus: $model->status,
                    userEmail: $user->email ?? '',
                    userName: $user->name ?? '',
                    total: $model->total,
                ),
            );
        }
    }
}
