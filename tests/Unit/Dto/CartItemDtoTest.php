<?php

declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\CartItem\CartItemDto;
use App\Dto\CartItem\CartItemFiltersDto;
use App\Dto\CartItem\CartItemResponseDto;
use App\Models\CartItem;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class CartItemDtoTest extends TestCase
{
    public function testCartItemDtoToArrayReturnsCorrectStructure(): void
    {
        $dto = new CartItemDto(userId: 1, bookId: 5, quantity: 3);

        $result = $dto->toArray();

        self::assertSame([
            'user_id' => 1,
            'book_id' => 5,
            'quantity' => 3,
        ], $result);
    }

    public function testCartItemDtoStoresPropertiesCorrectly(): void
    {
        $dto = new CartItemDto(userId: 7, bookId: 12, quantity: 4);

        self::assertSame(7, $dto->userId);
        self::assertSame(12, $dto->bookId);
        self::assertSame(4, $dto->quantity);
    }

    public function testCartItemFiltersDtoHasCorrectDefaults(): void
    {
        $dto = new CartItemFiltersDto();

        self::assertNull($dto->search);
        self::assertSame(15, $dto->perPage);
        self::assertSame('id', $dto->sortBy);
        self::assertSame('asc', $dto->sortDirection);
    }

    public function testCartItemFiltersDtoAcceptsCustomValues(): void
    {
        $dto = new CartItemFiltersDto(
            search: '5',
            perPage: 25,
            sortBy: 'quantity',
            sortDirection: 'desc',
        );

        self::assertSame('5', $dto->search);
        self::assertSame(25, $dto->perPage);
        self::assertSame('quantity', $dto->sortBy);
        self::assertSame('desc', $dto->sortDirection);
    }

    public function testCartItemResponseDtoFromModel(): void
    {
        $cartItem = new CartItem();
        $cartItem->id = 3;
        $cartItem->user_id = 2;
        $cartItem->book_id = 8;
        $cartItem->quantity = 5;
        $cartItem->created_at = now()->setDateTimeFrom('2024-03-01 10:00:00');
        $cartItem->updated_at = now()->setDateTimeFrom('2024-04-01 12:00:00');

        $dto = CartItemResponseDto::fromModel($cartItem);

        self::assertSame(3, $dto->id);
        self::assertSame(2, $dto->userId);
        self::assertSame(8, $dto->bookId);
        self::assertSame(5, $dto->quantity);
        self::assertSame('2024-03-01 10:00:00', $dto->createdAt);
        self::assertSame('2024-04-01 12:00:00', $dto->updatedAt);
    }
}
