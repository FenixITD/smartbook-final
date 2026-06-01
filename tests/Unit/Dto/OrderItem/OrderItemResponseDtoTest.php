<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\OrderItem;

use App\Dto\OrderItem\OrderItemResponseDto;
use App\Models\OrderItem;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class OrderItemResponseDtoTest extends TestCase
{
    public function test_from_model_creates_dto_with_full_data(): void
    {
        $orderItem = new OrderItem();
        $orderItem->id = 50;
        $orderItem->order_id = 10;
        $orderItem->book_id = 42;
        $orderItem->quantity = 2;
        $orderItem->price_at_purchase = 19.99;
        $orderItem->created_at = Carbon::parse('2026-06-01 12:00:00');
        $orderItem->updated_at = Carbon::parse('2026-06-01 12:30:00');

        $dto = OrderItemResponseDto::fromModel($orderItem);

        $this->assertSame(50, $dto->id);
        $this->assertSame(10, $dto->orderId);
        $this->assertSame(42, $dto->bookId);
        $this->assertSame(2, $dto->quantity);
        $this->assertSame(19.99, $dto->priceAtPurchase);
        $this->assertSame('2026-06-01 12:00:00', $dto->createdAt);
        $this->assertSame('2026-06-01 12:30:00', $dto->updatedAt);
    }

    public function test_from_model_creates_dto_with_null_timestamps(): void
    {
        $orderItem = new OrderItem();
        $orderItem->id = 51;
        $orderItem->order_id = 11;
        $orderItem->book_id = 43;
        $orderItem->quantity = 1;
        $orderItem->price_at_purchase = 9.99;
        $orderItem->created_at = null;
        $orderItem->updated_at = null;

        $dto = OrderItemResponseDto::fromModel($orderItem);

        $this->assertSame(51, $dto->id);
        $this->assertSame('', $dto->createdAt);
        $this->assertSame('', $dto->updatedAt);
    }
}
