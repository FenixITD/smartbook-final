<?php

declare(strict_types=1);

namespace App\DTO\OrderItem;

use App\Http\Requests\OrderItem\OrderItemDataRequest;

final readonly class OrderItemDTO
{
    public function __construct(
        public int $orderId,
        public int $bookId,
        public int $quantity,
        public float $priceAtPurchase,
    ) {}

    public static function fromRequest(OrderItemDataRequest $request): self
    {
        return new self(
            orderId: $request->integer('orderId'),
            bookId: $request->integer('bookId'),
            quantity: $request->integer('quantity'),
            priceAtPurchase: $request->integer('priceAtPurchase'),
        );
    }
}
