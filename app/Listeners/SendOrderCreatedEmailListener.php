<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderCreatedEvent;
use App\Mail\OrderStatusMail;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class SendOrderCreatedEmailListener implements ShouldQueue, ShouldBeUnique
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public int $tries = 3;

    public int $backoff = 60;

    public function handle(OrderCreatedEvent $event): void
    {
        Mail::to($event->dto->userEmail)
            ->send(new OrderStatusMail($event->dto));
    }

    public function failed(OrderCreatedEvent $event, Throwable $exception): void
    {
        report($exception);
    }
}
