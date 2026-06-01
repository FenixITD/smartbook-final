<?php

declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\PaginatedResponseDto;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

final class PaginatedResponseDtoTest extends TestCase
{
    public function test_from_paginator_creates_dto_correctly(): void
    {
        $items = ['item1', 'item2'];

        $paginator = new LengthAwarePaginator(
            items: $items,
            total: 50,
            perPage: 15,
            currentPage: 2
        );

        $dto = PaginatedResponseDto::fromPaginator($paginator);

        $this->assertSame($items, $dto->items);
        $this->assertSame(50, $dto->total);
        $this->assertSame(15, $dto->perPage);
        $this->assertSame(2, $dto->currentPage);
        $this->assertSame(4, $dto->lastPage);
        $this->assertIsString($dto->links);

        $this->assertStringContainsString('role="navigation"', $dto->links);
        $this->assertStringContainsString('aria-label="Pagination Navigation"', $dto->links);
    }

    public function test_create_generates_dto_from_raw_data_with_links(): void
    {
        $items = ['raw_item1', 'raw_item2'];

        $dto = PaginatedResponseDto::create(
            items: $items,
            total: 100,
            perPage: 20,
            currentPage: 3
        );

        $this->assertSame($items, $dto->items);
        $this->assertSame(100, $dto->total);
        $this->assertSame(20, $dto->perPage);
        $this->assertSame(3, $dto->currentPage);
        $this->assertSame(5, $dto->lastPage);
        $this->assertIsString($dto->links);

        $this->assertStringContainsString('role="navigation"', $dto->links);
    }

    public function test_empty_creates_dto_with_zero_values(): void
    {
        $dto = PaginatedResponseDto::empty(perPage: 25);

        $this->assertSame([], $dto->items);
        $this->assertSame(0, $dto->total);
        $this->assertSame(25, $dto->perPage);
        $this->assertSame(1, $dto->currentPage);
        $this->assertSame(1, $dto->lastPage);
        $this->assertSame('', $dto->links);
    }
}
