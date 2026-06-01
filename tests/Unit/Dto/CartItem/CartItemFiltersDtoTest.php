<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\CartItem;

use App\Dto\CartItem\CartItemFiltersDto;
use Tests\TestCase;

final class CartItemFiltersDtoTest extends TestCase
{
    public function test_cart_item_filters_dto_initializes_with_defaults(): void
    {
        $dto = new CartItemFiltersDto();

        $this->assertNull($dto->id);
        $this->assertNull($dto->search);
        $this->assertSame(15, $dto->perPage);
        $this->assertSame('id', $dto->sortBy);
        $this->assertSame('asc', $dto->sortDirection);
    }

    public function test_cart_item_filters_dto_initializes_with_custom_values(): void
    {
        $dto = new CartItemFiltersDto(
            42,
            'Harry Potter',
            50,
            'quantity',
            'desc'
        );

        $this->assertSame(42, $dto->id);
        $this->assertSame('Harry Potter', $dto->search);
        $this->assertSame(50, $dto->perPage);
        $this->assertSame('quantity', $dto->sortBy);
        $this->assertSame('desc', $dto->sortDirection);
    }
}
