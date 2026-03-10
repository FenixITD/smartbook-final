<?php

declare(strict_types=1);

namespace App\DTO\OrderItem;

use App\Models\OrderItem;

final readonly class OrderItemResponseDTO
{
    public function __construct(
        public int $id,
        public int $orderId,
        public int $bookId,
        public int $quantity,
        public float $priceAtPurchase,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromModel(OrderItem $orderItem): self
    {
        return new self(
            id: $orderItem->id,
            orderId: (int) $orderItem->userId,
            bookId: (int) $orderItem->bookId,
            quantity: $orderItem->quantity,
            priceAtPurchase: (float) $orderItem->priceAtPurchase,
            createdAt: $orderItem->created_at->toDateTimeString(),
            updatedAt: $orderItem->updated_at->toDateTimeString(),
        );
    }
}
