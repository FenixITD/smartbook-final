<?php

declare(strict_types=1);

namespace App\Dto\OrderItem;

final readonly class OrderItemDto
{
    public function __construct(
        public int $orderId,
        public int $bookId,
        public int $quantity,
        public string $priceAtPurchase,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'book_id' => $this->bookId,
            'quantity' => $this->quantity,
            'price_at_purchase' => $this->priceAtPurchase,
        ];
    }
}
