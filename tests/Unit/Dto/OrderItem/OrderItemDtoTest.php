<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\OrderItem;

use App\Dto\OrderItem\OrderItemDto;
use Tests\TestCase;

final class OrderItemDtoTest extends TestCase
{
    public function test_order_item_dto_initializes_and_returns_array(): void
    {
        $dto = new OrderItemDto(
            orderId: 10,
            bookId: 42,
            quantity: 3,
            priceAtPurchase: 25.50,
        );

        $this->assertSame(10, $dto->orderId);
        $this->assertSame(42, $dto->bookId);
        $this->assertSame(3, $dto->quantity);
        $this->assertSame(25.50, $dto->priceAtPurchase);

        $this->assertSame([
            'order_id' => 10,
            'book_id' => 42,
            'quantity' => 3,
            'price_at_purchase' => 25.50,
        ], $dto->toArray());
    }
}
