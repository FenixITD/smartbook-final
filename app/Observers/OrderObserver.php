<?php

declare(strict_types=1);

namespace App\Observers;

use App\Dto\Order\OrderStatusChangedDto;
use App\Enums\OrderStatusEnum;
use App\Events\OrderCreatedEvent;
use App\Events\OrderStatusChangedEvent;
use App\Models\Order;

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

        // Прерываем, если не изменился ни статус, ни метод оплаты
        if (!$statusChanged && !$paymentChanged) {
            return;
        }

        $newStatus = OrderStatusEnum::tryFrom($model->status);

        // Если метод оплаты НЕ менялся, проверяем, нужно ли отправлять уведомление для нового статуса (например, для Pending это false)
        if (!$paymentChanged && ($newStatus === null || !$newStatus->shouldNotify())) {
            return;
        }

        $user = $model->user;
        $originalStatus = $model->getOriginal('status');

        if ($user !== null) {
            OrderStatusChangedEvent::dispatch(
                new OrderStatusChangedDto(
                    orderId: $model->id,
                    oldStatus: $originalStatus ?? '',
                    newStatus: $model->status,
                    userEmail: $user->email ?? '',
                    userName: $user->name ?? '',
                    total: $model->total,
                ),
            );
        }
    }
}
