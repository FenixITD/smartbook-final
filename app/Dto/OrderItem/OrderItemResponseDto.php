<?php

declare(strict_types=1);

namespace App\Dto\OrderItem;

use App\Models\OrderItem;

final readonly class OrderItemResponseDto
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
            orderId: (int) $orderItem->order_id,
            bookId: (int) $orderItem->book_id,
            quantity: (int) $orderItem->quantity,
            priceAtPurchase: (float) $orderItem->price_at_purchase,
            createdAt: $orderItem->created_at->toDateTimeString(),
            updatedAt: $orderItem->updated_at->toDateTimeString(),
        );
    }
}
