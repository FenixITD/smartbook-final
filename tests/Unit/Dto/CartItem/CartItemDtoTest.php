<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\CartItem;

use App\Dto\CartItem\CartItemDto;
use Tests\TestCase;

final class CartItemDtoTest extends TestCase
{
    public function test_cart_item_dto_initializes_and_returns_array(): void
    {
        $dto = new CartItemDto(
            5,
            12,
            2
        );

        $this->assertSame(5, $dto->userId);
        $this->assertSame(12, $dto->bookId);
        $this->assertSame(2, $dto->quantity);

        $this->assertSame([
            'user_id' => 5,
            'book_id' => 12,
            'quantity' => 2,
        ], $dto->toArray());
    }
}
