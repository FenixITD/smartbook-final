<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\CartItem;

use App\Dto\CartItem\CartItemResponseDto;
use App\Models\CartItem;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class CartItemResponseDtoTest extends TestCase
{
    public function test_from_model_creates_dto_with_full_data(): void
    {
        $cartItem = new CartItem();
        $cartItem->id = 1;
        $cartItem->user_id = 10;
        $cartItem->book_id = 20;
        $cartItem->quantity = 3;
        $cartItem->created_at = Carbon::parse('2026-06-01 10:00:00');
        $cartItem->updated_at = Carbon::parse('2026-06-02 10:00:00');

        $dto = CartItemResponseDto::fromModel($cartItem);

        $this->assertSame(1, $dto->id);
        $this->assertSame(10, $dto->userId);
        $this->assertSame(20, $dto->bookId);
        $this->assertSame(3, $dto->quantity);
        $this->assertSame('2026-06-01 10:00:00', $dto->createdAt);
        $this->assertSame('2026-06-02 10:00:00', $dto->updatedAt);
    }

    public function test_from_model_creates_dto_with_null_dates(): void
    {
        $cartItem = new CartItem();
        $cartItem->id = 2;
        $cartItem->user_id = 15;
        $cartItem->book_id = 25;
        $cartItem->quantity = 1;
        $cartItem->created_at = null;
        $cartItem->updated_at = null;

        $dto = CartItemResponseDto::fromModel($cartItem);

        $this->assertSame(2, $dto->id);
        $this->assertSame(15, $dto->userId);
        $this->assertSame(25, $dto->bookId);
        $this->assertSame(1, $dto->quantity);
        $this->assertSame('', $dto->createdAt);
        $this->assertSame('', $dto->updatedAt);
    }
}
