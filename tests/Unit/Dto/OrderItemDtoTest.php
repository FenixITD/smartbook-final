<?php

declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\OrderItem\OrderItemDto;
use App\Dto\OrderItem\OrderItemFiltersDto;
use App\Dto\OrderItem\OrderItemResponseDto;
use App\Models\OrderItem;
use Tests\TestCase;

class OrderItemDtoTest extends TestCase
{
    public function test_order_item_dto_to_array_returns_correct_structure(): void
    {
        $dto = new OrderItemDto(
            orderId: 1,
            bookId: 2,
            quantity: 3,
            priceAtPurchase: 29.99,
        );

        $result = $dto->toArray();

        $this->assertSame([
            'order_id' => 1,
            'book_id' => 2,
            'quantity' => 3,
            'price_at_purchase' => 29.99,
        ], $result);
    }

    public function test_order_item_dto_stores_properties_correctly(): void
    {
        $dto = new OrderItemDto(
            orderId: 5,
            bookId: 10,
            quantity: 7,
            priceAtPurchase: 49.99,
        );

        $this->assertSame(5, $dto->orderId);
        $this->assertSame(10, $dto->bookId);
        $this->assertSame(7, $dto->quantity);
        $this->assertSame(49.99, $dto->priceAtPurchase);
    }

    public function test_order_item_filters_dto_has_correct_defaults(): void
    {
        $dto = new OrderItemFiltersDto;

        $this->assertNull($dto->search);
        $this->assertSame(15, $dto->perPage);
        $this->assertSame('id', $dto->sortBy);
        $this->assertSame('asc', $dto->sortDirection);
    }

    public function test_order_item_filters_dto_accepts_custom_values(): void
    {
        $dto = new OrderItemFiltersDto(
            search: '42',
            perPage: 25,
            sortBy: 'quantity',
            sortDirection: 'desc',
        );

        $this->assertSame('42', $dto->search);
        $this->assertSame(25, $dto->perPage);
        $this->assertSame('quantity', $dto->sortBy);
        $this->assertSame('desc', $dto->sortDirection);
    }

    public function test_order_item_response_dto_from_model(): void
    {
        $orderItem = new OrderItem;
        $orderItem->id = 8;
        $orderItem->order_id = 3;
        $orderItem->book_id = 11;
        $orderItem->quantity = 2;
        $orderItem->price_at_purchase = 19.99;
        $orderItem->created_at = now()->setDateTimeFrom('2024-04-01 10:00:00');
        $orderItem->updated_at = now()->setDateTimeFrom('2024-04-05 16:00:00');

        $dto = OrderItemResponseDto::fromModel($orderItem);

        $this->assertSame(8, $dto->id);
        $this->assertSame(3, $dto->orderId);
        $this->assertSame(11, $dto->bookId);
        $this->assertSame(2, $dto->quantity);
        $this->assertSame(19.99, $dto->priceAtPurchase);
        $this->assertSame('2024-04-01 10:00:00', $dto->createdAt);
        $this->assertSame('2024-04-05 16:00:00', $dto->updatedAt);
    }

    public function test_order_item_response_dto_casts_fields_to_correct_types(): void
    {
        $orderItem = new OrderItem;
        $orderItem->id = 1;
        $orderItem->order_id = 1;
        $orderItem->book_id = 1;
        $orderItem->quantity = 1;
        $orderItem->price_at_purchase = 9.99;
        $orderItem->created_at = now();
        $orderItem->updated_at = now();

        $dto = OrderItemResponseDto::fromModel($orderItem);

        $this->assertIsInt($dto->orderId);
        $this->assertIsInt($dto->bookId);
        $this->assertIsInt($dto->quantity);
        $this->assertIsFloat($dto->priceAtPurchase);
    }
}
