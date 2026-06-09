<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Book;

use App\Dto\Book\BookFiltersDto;
use Tests\TestCase;

final class BookFiltersDtoTest extends TestCase
{
    public function test_book_filters_dto_initializes_with_defaults(): void
    {
        $dto = new BookFiltersDto();

        $this->assertNull($dto->search);
        $this->assertSame(15, $dto->perPage);
        $this->assertSame('id', $dto->sortBy);
        $this->assertSame('desc', $dto->sortDirection);
    }

    public function test_book_filters_dto_initializes_with_custom_values(): void
    {
        $dto = new BookFiltersDto('Test', 50, 'name', 'asc');

        $this->assertSame('Test', $dto->search);
        $this->assertSame(50, $dto->perPage);
        $this->assertSame('name', $dto->sortBy);
        $this->assertSame('asc', $dto->sortDirection);
    }
}
