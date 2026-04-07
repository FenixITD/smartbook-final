<?php

declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\CartItem\CartItemDto;
use App\Dto\CartItem\CartItemFiltersDto;
use App\Dto\CartItem\CartItemResponseDto;
use App\Models\CartItem;
use Tests\TestCase;

class CartItemDtoTest extends TestCase
{
    public function test_cart_item_dto_to_array_returns_correct_structure(): void
    {
        $dto = new CartItemDto(userId: 1, bookId: 5, quantity: 3);

        $result = $dto->toArray();

        $this->assertSame([
            'user_id' => 1,
            'book_id' => 5,
            'quantity' => 3,
        ], $result);
    }

    public function test_cart_item_dto_stores_properties_correctly(): void
    {
        $dto = new CartItemDto(userId: 7, bookId: 12, quantity: 4);

        $this->assertSame(7, $dto->userId);
        $this->assertSame(12, $dto->bookId);
        $this->assertSame(4, $dto->quantity);
    }

    public function test_cart_item_filters_dto_has_correct_defaults(): void
    {
        $dto = new CartItemFiltersDto;

        $this->assertNull($dto->search);
        $this->assertSame(15, $dto->perPage);
        $this->assertSame('id', $dto->sortBy);
        $this->assertSame('asc', $dto->sortDirection);
    }

    public function test_cart_item_filters_dto_accepts_custom_values(): void
    {
        $dto = new CartItemFiltersDto(
            search: '5',
            perPage: 25,
            sortBy: 'quantity',
            sortDirection: 'desc',
        );

        $this->assertSame('5', $dto->search);
        $this->assertSame(25, $dto->perPage);
        $this->assertSame('quantity', $dto->sortBy);
        $this->assertSame('desc', $dto->sortDirection);
    }

    public function test_cart_item_response_dto_from_model(): void
    {
        $cartItem = new CartItem;
        $cartItem->id = 3;
        $cartItem->user_id = 2;
        $cartItem->book_id = 8;
        $cartItem->quantity = 5;
        $cartItem->created_at = now()->setDateTimeFrom('2024-03-01 10:00:00');
        $cartItem->updated_at = now()->setDateTimeFrom('2024-04-01 12:00:00');

        $dto = CartItemResponseDto::fromModel($cartItem);

        $this->assertSame(3, $dto->id);
        $this->assertSame(2, $dto->userId);
        $this->assertSame(8, $dto->bookId);
        $this->assertSame(5, $dto->quantity);
        $this->assertSame('2024-03-01 10:00:00', $dto->createdAt);
        $this->assertSame('2024-04-01 12:00:00', $dto->updatedAt);
    }
}
