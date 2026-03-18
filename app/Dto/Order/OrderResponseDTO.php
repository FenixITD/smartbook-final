<?php

declare(strict_types=1);

namespace App\Dto\Order;

use App\Models\Order;

final readonly class OrderResponseDTO
{
    public function __construct(
        public int $id,
        public int $userId,
        public float $total,
        public string $status,
        public string $shippingAddress,
        public string $paymentMethod,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromModel(Order $order): self
    {
        return new self(
            id: $order->id,
            userId: (int) $order->user_id,
            total: (float) $order->total,
            status: (string) $order->status,
            shippingAddress: (string) $order->shipping_address,
            paymentMethod: (string) $order->payment_method,
            createdAt: $order->created_at->toDateTimeString(),
            updatedAt: $order->updated_at->toDateTimeString(),
        );
    }
}
