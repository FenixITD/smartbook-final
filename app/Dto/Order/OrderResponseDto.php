<?php

declare(strict_types=1);

namespace App\Dto\Order;

use App\Models\Order;

final readonly class OrderResponseDto
{
    public static function fromModel(Order $order): self
    {
        return new self(
            id: $order->id,
            userId: $order->user_id,
            userName: $order->user?->name ?? '',
            total: $order->total,
            status: $order->status,
            shippingAddress: $order->shipping_address ?? '',
            paymentMethod: $order->payment_method ?? '',
            createdAt: $order->created_at?->toDateTimeString() ?? '',
            updatedAt: $order->updated_at?->toDateTimeString() ?? '',
        );
    }

    public function __construct(
        public int $id,
        public int $userId,
        public string $userName,
        public float $total,
        public string $status,
        public string $shippingAddress,
        public string $paymentMethod,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }
}
