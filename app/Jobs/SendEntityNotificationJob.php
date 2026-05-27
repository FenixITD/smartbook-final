<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\EntityNotificationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

final class SendEntityNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Number of attempts on failure.
     */
    public int $tries = 3;

    /**
     * Task execution timeout (seconds).
     */
    public int $timeout = 30;

    public function __construct(
        private readonly string $entityType,
        private readonly string $action,
        private readonly array $entityData,
        private readonly string $performedAt,
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        $recipient = config('mail.notification_recipient');

        if (empty($recipient)) {
            return;
        }

        Mail::to($recipient)->send(new EntityNotificationMail(
            entityType: $this->entityType,
            action: $this->action,
            entityData: $this->entityData,
            performedAt: $this->performedAt,
        ));
    }
}
