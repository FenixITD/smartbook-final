<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Review;

use App\Dto\Review\ReviewFiltersDto;
use Tests\TestCase;

final class ReviewFiltersDtoTest extends TestCase
{
    public function test_review_filters_dto_initializes_with_defaults(): void
    {
        $dto = new ReviewFiltersDto();

        $this->assertNull($dto->id);
        $this->assertNull($dto->search);
        $this->assertSame(15, $dto->perPage);
        $this->assertSame('id', $dto->sortBy);
        $this->assertSame('asc', $dto->sortDirection);
    }

    public function test_review_filters_dto_initializes_with_custom_values(): void
    {
        $dto = new ReviewFiltersDto(
            id: 5,
            search: 'excellent',
            perPage: 30,
            sortBy: 'rating',
            sortDirection: 'desc'
        );

        $this->assertSame(5, $dto->id);
        $this->assertSame('excellent', $dto->search);
        $this->assertSame(30, $dto->perPage);
        $this->assertSame('rating', $dto->sortBy);
        $this->assertSame('desc', $dto->sortDirection);
    }
}
