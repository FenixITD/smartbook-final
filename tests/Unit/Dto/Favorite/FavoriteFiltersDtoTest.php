<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Favorite;

use App\Dto\Favorite\FavoriteFiltersDto;
use Tests\TestCase;

final class FavoriteFiltersDtoTest extends TestCase
{
    public function test_favorite_filters_dto_initializes_with_defaults(): void
    {
        $dto = new FavoriteFiltersDto();

        $this->assertNull($dto->id);
        $this->assertNull($dto->search);
        $this->assertSame(15, $dto->perPage);
        $this->assertSame('id', $dto->sortBy);
        $this->assertSame('asc', $dto->sortDirection);
    }

    public function test_favorite_filters_dto_initializes_with_custom_values(): void
    {
        $dto = new FavoriteFiltersDto(
            id: 5,
            search: 'Detective',
            perPage: 30,
            sortBy: 'created_at',
            sortDirection: 'desc'
        );

        $this->assertSame(5, $dto->id);
        $this->assertSame('Detective', $dto->search);
        $this->assertSame(30, $dto->perPage);
        $this->assertSame('created_at', $dto->sortBy);
        $this->assertSame('desc', $dto->sortDirection);
    }
}
