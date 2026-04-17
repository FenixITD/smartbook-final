<?php

declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\Genre\GenreDto;
use App\Dto\Genre\GenreFiltersDto;
use App\Dto\Genre\GenreResponseDto;
use App\Models\Genre;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class GenreDtoTest extends TestCase
{
    public function testGenreDtoToArrayReturnsCorrectStructure(): void
    {
        $dto = new GenreDto(
            name: 'Science Fiction',
            slug: 'science-fiction',
            description: 'A genre about science and the future.',
        );

        $result = $dto->toArray();

        self::assertSame([
            'name' => 'Science Fiction',
            'slug' => 'science-fiction',
            'description' => 'A genre about science and the future.',
        ], $result);
    }

    public function testGenreDtoStoresPropertiesCorrectly(): void
    {
        $dto = new GenreDto(
            name: 'Fantasy',
            slug: 'fantasy',
            description: 'A genre with magic and mythical creatures.',
        );

        self::assertSame('Fantasy', $dto->name);
        self::assertSame('fantasy', $dto->slug);
        self::assertSame('A genre with magic and mythical creatures.', $dto->description);
    }

    public function testGenreFiltersDtoHasCorrectDefaults(): void
    {
        $dto = new GenreFiltersDto();

        self::assertNull($dto->search);
        self::assertSame(15, $dto->perPage);
        self::assertSame('id', $dto->sortBy);
        self::assertSame('asc', $dto->sortDirection);
    }

    public function testGenreFiltersDtoAcceptsCustomValues(): void
    {
        $dto = new GenreFiltersDto(
            search: 'Fiction',
            perPage: 30,
            sortBy: 'name',
            sortDirection: 'desc',
        );

        self::assertSame('Fiction', $dto->search);
        self::assertSame(30, $dto->perPage);
        self::assertSame('name', $dto->sortBy);
        self::assertSame('desc', $dto->sortDirection);
    }

    public function testGenreResponseDtoFromModel(): void
    {
        $genre = new Genre();
        $genre->id = 3;
        $genre->name = 'Horror';
        $genre->slug = 'horror';
        $genre->description = 'A genre designed to frighten.';
        $genre->created_at = now()->setDateTimeFrom('2024-01-01 10:00:00');
        $genre->updated_at = now()->setDateTimeFrom('2024-06-01 12:00:00');

        $dto = GenreResponseDto::fromModel($genre);

        self::assertSame(3, $dto->id);
        self::assertSame('Horror', $dto->name);
        self::assertSame('horror', $dto->slug);
        self::assertSame('A genre designed to frighten.', $dto->description);
        self::assertSame('2024-01-01 10:00:00', $dto->createdAt);
        self::assertSame('2024-06-01 12:00:00', $dto->updatedAt);
    }

    public function testGenreResponseDtoCastsFieldsToString(): void
    {
        $genre = new Genre();
        $genre->id = 1;
        $genre->name = 'Mystery';
        $genre->slug = 'mystery';
        $genre->description = 'A genre full of intrigue.';
        $genre->created_at = now();
        $genre->updated_at = now();

        $dto = GenreResponseDto::fromModel($genre);

        self::assertIsString($dto->name);
        self::assertIsString($dto->slug);
        self::assertIsString($dto->description);
    }
}
