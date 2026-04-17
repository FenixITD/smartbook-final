<?php

declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\Favorite\FavoriteDto;
use App\Dto\Favorite\FavoriteFiltersDto;
use App\Dto\Favorite\FavoriteResponseDto;
use App\Models\Favorite;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class FavoriteDtoTest extends TestCase
{
    public function testFavoriteDtoToArrayReturnsCorrectStructure(): void
    {
        $dto = new FavoriteDto(userId: 1, bookId: 5);

        $result = $dto->toArray();

        self::assertSame([
            'user_id' => 1,
            'book_id' => 5,
        ], $result);
    }

    public function testFavoriteDtoStoresPropertiesCorrectly(): void
    {
        $dto = new FavoriteDto(userId: 3, bookId: 7);

        self::assertSame(3, $dto->userId);
        self::assertSame(7, $dto->bookId);
    }

    public function testFavoriteFiltersDtoHasCorrectDefaults(): void
    {
        $dto = new FavoriteFiltersDto();

        self::assertNull($dto->search);
        self::assertSame(15, $dto->perPage);
        self::assertSame('id', $dto->sortBy);
        self::assertSame('asc', $dto->sortDirection);
    }

    public function testFavoriteFiltersDtoAcceptsCustomValues(): void
    {
        $dto = new FavoriteFiltersDto(
            search: '3',
            perPage: 25,
            sortBy: 'book_id',
            sortDirection: 'desc',
        );

        self::assertSame('3', $dto->search);
        self::assertSame(25, $dto->perPage);
        self::assertSame('book_id', $dto->sortBy);
        self::assertSame('desc', $dto->sortDirection);
    }

    public function testFavoriteResponseDtoFromModel(): void
    {
        $favorite = new Favorite();
        $favorite->id = 4;
        $favorite->user_id = 2;
        $favorite->book_id = 9;
        $favorite->created_at = now()->setDateTimeFrom('2024-03-01 10:00:00');
        $favorite->updated_at = now()->setDateTimeFrom('2024-04-01 12:00:00');

        $dto = FavoriteResponseDto::fromModel($favorite);

        self::assertSame(4, $dto->id);
        self::assertSame(2, $dto->userId);
        self::assertSame(9, $dto->bookId);
        self::assertSame('2024-03-01 10:00:00', $dto->createdAt);
        self::assertSame('2024-04-01 12:00:00', $dto->updatedAt);
    }
}
