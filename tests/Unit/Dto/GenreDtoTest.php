<?php

declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\Genre\GenreDto;
use App\Dto\Genre\GenreFiltersDto;
use App\Dto\Genre\GenreResponseDto;
use App\Models\Genre;
use Tests\TestCase;

class GenreDtoTest extends TestCase
{
    public function test_genre_dto_to_array_returns_correct_structure(): void
    {
        $dto = new GenreDto(
            name: 'Science Fiction',
            slug: 'science-fiction',
            description: 'A genre about science and the future.',
        );

        $result = $dto->toArray();

        $this->assertSame([
            'name' => 'Science Fiction',
            'slug' => 'science-fiction',
            'description' => 'A genre about science and the future.',
        ], $result);
    }

    public function test_genre_dto_stores_properties_correctly(): void
    {
        $dto = new GenreDto(
            name: 'Fantasy',
            slug: 'fantasy',
            description: 'A genre with magic and mythical creatures.',
        );

        $this->assertSame('Fantasy', $dto->name);
        $this->assertSame('fantasy', $dto->slug);
        $this->assertSame('A genre with magic and mythical creatures.', $dto->description);
    }

    public function test_genre_filters_dto_has_correct_defaults(): void
    {
        $dto = new GenreFiltersDto;

        $this->assertNull($dto->search);
        $this->assertSame(15, $dto->perPage);
        $this->assertSame('id', $dto->sortBy);
        $this->assertSame('asc', $dto->sortDirection);
    }

    public function test_genre_filters_dto_accepts_custom_values(): void
    {
        $dto = new GenreFiltersDto(
            search: 'Fiction',
            perPage: 30,
            sortBy: 'name',
            sortDirection: 'desc',
        );

        $this->assertSame('Fiction', $dto->search);
        $this->assertSame(30, $dto->perPage);
        $this->assertSame('name', $dto->sortBy);
        $this->assertSame('desc', $dto->sortDirection);
    }

    public function test_genre_response_dto_from_model(): void
    {
        $genre = new Genre;
        $genre->id = 3;
        $genre->name = 'Horror';
        $genre->slug = 'horror';
        $genre->description = 'A genre designed to frighten.';
        $genre->created_at = now()->setDateTimeFrom('2024-01-01 10:00:00');
        $genre->updated_at = now()->setDateTimeFrom('2024-06-01 12:00:00');

        $dto = GenreResponseDto::fromModel($genre);

        $this->assertSame(3, $dto->id);
        $this->assertSame('Horror', $dto->name);
        $this->assertSame('horror', $dto->slug);
        $this->assertSame('A genre designed to frighten.', $dto->description);
        $this->assertSame('2024-01-01 10:00:00', $dto->createdAt);
        $this->assertSame('2024-06-01 12:00:00', $dto->updatedAt);
    }

    public function test_genre_response_dto_casts_fields_to_string(): void
    {
        $genre = new Genre;
        $genre->id = 1;
        $genre->name = 'Mystery';
        $genre->slug = 'mystery';
        $genre->description = 'A genre full of intrigue.';
        $genre->created_at = now();
        $genre->updated_at = now();

        $dto = GenreResponseDto::fromModel($genre);

        $this->assertIsString($dto->name);
        $this->assertIsString($dto->slug);
        $this->assertIsString($dto->description);
    }
}
