<?php

declare(strict_types=1);

namespace App\Observers;

use App\Dto\Order\OrderStatusChangedDto;
use App\Enums\OrderStatusEnum;
use App\Events\OrderCreatedEvent;
use App\Events\OrderStatusChangedEvent;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;

final class OrderObserver extends BaseEntityObserver
{
    public function created(Model $model): void
    {
        parent::created($model);

        if (!$model instanceof Order) {
            return;
        }

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

    public function updated(Model $model): void
    {
        parent::updated($model);

        if (!$model instanceof Order) {
            return;
        }

        if ($model->wasRecentlyCreated) {
            return;
        }

        if (!$model->wasChanged('status')) {
            return;
        }

        $newStatus = OrderStatusEnum::tryFrom($model->status);

        // @phpstan-ignore identical.alwaysFalse
        if ($newStatus === null || !$newStatus->shouldNotify()) {
            return;
        }

        $user = $model->user;

        /** @var string|null $originalStatus */
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
