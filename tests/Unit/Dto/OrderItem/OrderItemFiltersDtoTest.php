<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\OrderItem;

use App\Dto\OrderItem\OrderItemFiltersDto;
use Tests\TestCase;

final class OrderItemFiltersDtoTest extends TestCase
{
    public function test_order_item_filters_dto_initializes_with_defaults(): void
    {
        $dto = new OrderItemFiltersDto();

        $this->assertNull($dto->id);
        $this->assertNull($dto->search);
        $this->assertSame(15, $dto->perPage);
        $this->assertSame('id', $dto->sortBy);
        $this->assertSame('asc', $dto->sortDirection);
    }

    public function test_order_item_filters_dto_initializes_with_custom_values(): void
    {
        $dto = new OrderItemFiltersDto(
            id: 100,
            search: 'Premium',
            perPage: 30,
            sortBy: 'quantity',
            sortDirection: 'desc'
        );

        $this->assertSame(100, $dto->id);
        $this->assertSame('Premium', $dto->search);
        $this->assertSame(30, $dto->perPage);
        $this->assertSame('quantity', $dto->sortBy);
        $this->assertSame('desc', $dto->sortDirection);
    }
}
