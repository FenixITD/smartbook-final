<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatusEnum: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Awaiting payment',
            self::Paid => 'Paid',
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => '#f59e0b',
            self::Paid => '#3b82f6',
            self::Shipped => '#8b5cf6',
            self::Delivered => '#22c55e',
            self::Cancelled => '#ef4444',
        };
    }

    public function userMessage(): string
    {
        return match ($this) {
            self::Pending => 'Your order has been received and is awaiting payment. Please complete payment so we can begin processing it.',
            self::Paid => 'Payment was successful! Your order is being processed and will be shipped soon.',
            self::Shipped => 'Your order has been shipped and is on its way. Expect delivery soon!',
            self::Delivered => 'Your order has been successfully delivered. Enjoy your reading! 📚',
            self::Cancelled => 'Your order has been canceled. If you have any questions, please contact customer support.',
        };
    }

    public function shouldNotify(): bool
    {
        return match ($this) {
            self::Pending => false,
            default => true,
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        if ($this === $newStatus) {
            return true;
        }

        return match ($this) {
            self::Pending => in_array($newStatus, [self::Paid, self::Cancelled], true),
            self::Paid => in_array($newStatus, [self::Shipped, self::Cancelled], true),
            self::Shipped => in_array($newStatus, [self::Delivered, self::Cancelled], true),
            self::Delivered => false,
            self::Cancelled => false,
        };
    }
}
