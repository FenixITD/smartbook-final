<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderStatusChangedEvent;
use App\Mail\OrderStatusMail;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class SendOrderStatusEmailListener implements ShouldQueue, ShouldBeUnique
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public int $tries = 3;

    public int $backoff = 60;

    // Уникальный ключ — один job на orderId в очереди
    public function uniqueId(OrderStatusChangedEvent $event): string
    {
        return 'order_status_' . $event->dto->orderId;
    }

    // Держать блокировку 60 секунд
    public function uniqueFor(): int
    {
        return 60;
    }

    public function handle(OrderStatusChangedEvent $event): void
    {
        Mail::to($event->dto->userEmail)
            ->send(new OrderStatusMail($event->dto));
    }

    public function failed(OrderStatusChangedEvent $event, Throwable $exception): void
    {
        report($exception);
    }
}
