<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Author;

use App\Dto\Author\AuthorFiltersDto;
use Tests\TestCase;

final class AuthorFiltersDtoTest extends TestCase
{
    public function test_author_filters_dto_initializes_with_defaults(): void
    {
        $dto = new AuthorFiltersDto();

        $this->assertNull($dto->search);
        $this->assertSame(15, $dto->perPage);
        $this->assertSame('id', $dto->sortBy);
        $this->assertSame('desc', $dto->sortDirection);
    }

    public function test_author_filters_dto_initializes_with_custom_values(): void
    {
        $dto = new AuthorFiltersDto('Test', 50, 'name', 'asc');

        $this->assertSame('Test', $dto->search);
        $this->assertSame(50, $dto->perPage);
        $this->assertSame('name', $dto->sortBy);
        $this->assertSame('asc', $dto->sortDirection);
    }
}
