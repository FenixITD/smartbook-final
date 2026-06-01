<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Order;

use App\Dto\Order\OrderFiltersDto;
use Tests\TestCase;

final class OrderFiltersDtoTest extends TestCase
{
    public function test_order_filters_dto_initializes_with_defaults(): void
    {
        $dto = new OrderFiltersDto();

        $this->assertNull($dto->id);
        $this->assertNull($dto->search);
        $this->assertSame(15, $dto->perPage);
        $this->assertSame('id', $dto->sortBy);
        $this->assertSame('asc', $dto->sortDirection);
    }

    public function test_order_filters_dto_initializes_with_custom_values(): void
    {
        $dto = new OrderFiltersDto(
            id: 42,
            search: 'Express',
            perPage: 50,
            sortBy: 'total',
            sortDirection: 'desc'
        );

        $this->assertSame(42, $dto->id);
        $this->assertSame('Express', $dto->search);
        $this->assertSame(50, $dto->perPage);
        $this->assertSame('total', $dto->sortBy);
        $this->assertSame('desc', $dto->sortDirection);
    }
}
