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
        $this->assertSame('asc', $dto->sortDirection);
    }

    public function test_book_filters_dto_initializes_with_custom_values(): void
    {
        $dto = new BookFiltersDto('Fantasy', 25, 'title', 'desc');

        $this->assertSame('Fantasy', $dto->search);
        $this->assertSame(25, $dto->perPage);
        $this->assertSame('title', $dto->sortBy);
        $this->assertSame('desc', $dto->sortDirection);
    }
}
