<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

final class EntityNotificationMail extends Mailable
{
    public function __construct(
        public readonly string $entityType,
        public readonly string $action,
        public readonly array  $entityData,
        public readonly string $performedAt,
    ) {}

    public function envelope(): Envelope
    {
        $actionLabel = match ($this->action) {
            'created' => 'created',
            'updated' => 'updated',
            'deleted' => 'deleted',
            default => $this->action,
        };

        return new Envelope(
            subject: "[SmartBook] {$this->entityType} {$actionLabel}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.entity-notification',
        );
    }
}
