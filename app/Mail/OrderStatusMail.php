<?php

declare(strict_types=1);

namespace App\Mail;

use App\Dto\Order\OrderStatusChangedDto;
use App\Enums\OrderStatusEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class OrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly OrderStatusChangedDto $dto,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Order status #{$this->dto->orderId} has been updated — SmartBook",
        );
    }

    public function content(): Content
    {
        $status = OrderStatusEnum::tryFrom($this->dto->newStatus);

        return new Content(
            view: 'emails.order-status',
            with: [
                'orderId' => $this->dto->orderId,
                'userName' => $this->dto->userName,
                'total' => $this->dto->total,
                'statusLabel' => $status?->label() ?? $this->dto->newStatus,
                'statusColor' => $status?->color() ?? '#6b7280',
                'statusMessage' => $status?->userMessage() ?? '',
            ],
        );
    }
}
