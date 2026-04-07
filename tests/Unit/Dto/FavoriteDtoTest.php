<?php

declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\Favorite\FavoriteDto;
use App\Dto\Favorite\FavoriteFiltersDto;
use App\Dto\Favorite\FavoriteResponseDto;
use App\Models\Favorite;
use Tests\TestCase;

class FavoriteDtoTest extends TestCase
{
    public function test_favorite_dto_to_array_returns_correct_structure(): void
    {
        $dto = new FavoriteDto(userId: 1, bookId: 5);

        $result = $dto->toArray();

        $this->assertSame([
            'user_id' => 1,
            'book_id' => 5,
        ], $result);
    }

    public function test_favorite_dto_stores_properties_correctly(): void
    {
        $dto = new FavoriteDto(userId: 3, bookId: 7);

        $this->assertSame(3, $dto->userId);
        $this->assertSame(7, $dto->bookId);
    }

    public function test_favorite_filters_dto_has_correct_defaults(): void
    {
        $dto = new FavoriteFiltersDto;

        $this->assertNull($dto->search);
        $this->assertSame(15, $dto->perPage);
        $this->assertSame('id', $dto->sortBy);
        $this->assertSame('asc', $dto->sortDirection);
    }

    public function test_favorite_filters_dto_accepts_custom_values(): void
    {
        $dto = new FavoriteFiltersDto(
            search: '3',
            perPage: 25,
            sortBy: 'book_id',
            sortDirection: 'desc',
        );

        $this->assertSame('3', $dto->search);
        $this->assertSame(25, $dto->perPage);
        $this->assertSame('book_id', $dto->sortBy);
        $this->assertSame('desc', $dto->sortDirection);
    }

    public function test_favorite_response_dto_from_model(): void
    {
        $favorite = new Favorite;
        $favorite->id = 4;
        $favorite->user_id = 2;
        $favorite->book_id = 9;
        $favorite->created_at = now()->setDateTimeFrom('2024-03-01 10:00:00');
        $favorite->updated_at = now()->setDateTimeFrom('2024-04-01 12:00:00');

        $dto = FavoriteResponseDto::fromModel($favorite);

        $this->assertSame(4, $dto->id);
        $this->assertSame(2, $dto->userId);
        $this->assertSame(9, $dto->bookId);
        $this->assertSame('2024-03-01 10:00:00', $dto->createdAt);
        $this->assertSame('2024-04-01 12:00:00', $dto->updatedAt);
    }
}
