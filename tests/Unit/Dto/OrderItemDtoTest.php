<?php

declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\OrderItem\OrderItemDto;
use App\Dto\OrderItem\OrderItemFiltersDto;
use App\Dto\OrderItem\OrderItemResponseDto;
use App\Models\OrderItem;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class OrderItemDtoTest extends TestCase
{
    public function testOrderItemDtoToArrayReturnsCorrectStructure(): void
    {
        $dto = new OrderItemDto(
            orderId: 1,
            bookId: 2,
            quantity: 3,
            priceAtPurchase: 29.99,
        );

        $result = $dto->toArray();

        self::assertSame([
            'order_id' => 1,
            'book_id' => 2,
            'quantity' => 3,
            'price_at_purchase' => 29.99,
        ], $result);
    }

    public function testOrderItemDtoStoresPropertiesCorrectly(): void
    {
        $dto = new OrderItemDto(
            orderId: 5,
            bookId: 10,
            quantity: 7,
            priceAtPurchase: 49.99,
        );

        self::assertSame(5, $dto->orderId);
        self::assertSame(10, $dto->bookId);
        self::assertSame(7, $dto->quantity);
        self::assertSame(49.99, $dto->priceAtPurchase);
    }

    public function testOrderItemFiltersDtoHasCorrectDefaults(): void
    {
        $dto = new OrderItemFiltersDto();

        self::assertNull($dto->search);
        self::assertSame(15, $dto->perPage);
        self::assertSame('id', $dto->sortBy);
        self::assertSame('asc', $dto->sortDirection);
    }

    public function testOrderItemFiltersDtoAcceptsCustomValues(): void
    {
        $dto = new OrderItemFiltersDto(
            search: '42',
            perPage: 25,
            sortBy: 'quantity',
            sortDirection: 'desc',
        );

        self::assertSame('42', $dto->search);
        self::assertSame(25, $dto->perPage);
        self::assertSame('quantity', $dto->sortBy);
        self::assertSame('desc', $dto->sortDirection);
    }

    public function testOrderItemResponseDtoFromModel(): void
    {
        $orderItem = new OrderItem();
        $orderItem->id = 8;
        $orderItem->order_id = 3;
        $orderItem->book_id = 11;
        $orderItem->quantity = 2;
        $orderItem->price_at_purchase = 19.99;
        $orderItem->created_at = now()->setDateTimeFrom('2024-04-01 10:00:00');
        $orderItem->updated_at = now()->setDateTimeFrom('2024-04-05 16:00:00');

        $dto = OrderItemResponseDto::fromModel($orderItem);

        self::assertSame(8, $dto->id);
        self::assertSame(3, $dto->orderId);
        self::assertSame(11, $dto->bookId);
        self::assertSame(2, $dto->quantity);
        self::assertSame(19.99, $dto->priceAtPurchase);
        self::assertSame('2024-04-01 10:00:00', $dto->createdAt);
        self::assertSame('2024-04-05 16:00:00', $dto->updatedAt);
    }

    public function testOrderItemResponseDtoCastsFieldsToCorrectTypes(): void
    {
        $orderItem = new OrderItem();
        $orderItem->id = 1;
        $orderItem->order_id = 1;
        $orderItem->book_id = 1;
        $orderItem->quantity = 1;
        $orderItem->price_at_purchase = 9.99;
        $orderItem->created_at = now();
        $orderItem->updated_at = now();

        $dto = OrderItemResponseDto::fromModel($orderItem);

        self::assertIsInt($dto->orderId);
        self::assertIsInt($dto->bookId);
        self::assertIsInt($dto->quantity);
        self::assertIsFloat($dto->priceAtPurchase);
    }
}
