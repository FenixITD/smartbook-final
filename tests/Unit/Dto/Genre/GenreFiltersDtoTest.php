<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Genre;

use App\Dto\Genre\GenreFiltersDto;
use Tests\TestCase;

final class GenreFiltersDtoTest extends TestCase
{
    public function test_genre_filters_dto_initializes_with_defaults(): void
    {
        $dto = new GenreFiltersDto();

        $this->assertNull($dto->search);
        $this->assertSame(15, $dto->perPage);
        $this->assertSame('id', $dto->sortBy);
        $this->assertSame('asc', $dto->sortDirection);
    }

    public function test_genre_filters_dto_initializes_with_custom_values(): void
    {
        $dto = new GenreFiltersDto('Cyberpunk', 50, 'name', 'desc');

        $this->assertSame('Cyberpunk', $dto->search);
        $this->assertSame(50, $dto->perPage);
        $this->assertSame('name', $dto->sortBy);
        $this->assertSame('desc', $dto->sortDirection);
    }
}
