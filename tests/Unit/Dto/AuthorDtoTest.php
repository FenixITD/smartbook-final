<?php

declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\Author\AuthorDto;
use App\Dto\Author\AuthorFiltersDto;
use App\Dto\Author\AuthorResponseDto;
use App\Models\Author;
use Tests\TestCase;

class AuthorDtoTest extends TestCase
{
    public function test_author_dto_to_array_returns_correct_structure(): void
    {
        $dto = new AuthorDto(name: 'Ivan Turgenev');

        $result = $dto->toArray();

        $this->assertSame(['name' => 'Ivan Turgenev'], $result);
    }

    public function test_author_dto_stores_name_correctly(): void
    {
        $dto = new AuthorDto(name: 'Mikhail Bulgakov');

        $this->assertSame('Mikhail Bulgakov', $dto->name);
    }

    public function test_author_filters_dto_has_correct_defaults(): void
    {
        $dto = new AuthorFiltersDto;

        $this->assertNull($dto->search);
        $this->assertSame(15, $dto->perPage);
        $this->assertSame('id', $dto->sortBy);
        $this->assertSame('asc', $dto->sortDirection);
    }

    public function test_author_filters_dto_accepts_custom_values(): void
    {
        $dto = new AuthorFiltersDto(
            search: 'Tolstoy',
            perPage: 30,
            sortBy: 'name',
            sortDirection: 'desc',
        );

        $this->assertSame('Tolstoy', $dto->search);
        $this->assertSame(30, $dto->perPage);
        $this->assertSame('name', $dto->sortBy);
        $this->assertSame('desc', $dto->sortDirection);
    }

    public function test_author_response_dto_from_model(): void
    {
        $author = new Author;
        $author->id = 5;
        $author->name = 'Boris Pasternak';
        $author->created_at = now()->setDateTimeFrom('2024-03-01 12:00:00');
        $author->updated_at = now()->setDateTimeFrom('2024-04-01 15:30:00');

        $dto = AuthorResponseDto::fromModel($author);

        $this->assertSame(5, $dto->id);
        $this->assertSame('Boris Pasternak', $dto->name);
        $this->assertSame('2024-03-01 12:00:00', $dto->createdAt);
        $this->assertSame('2024-04-01 15:30:00', $dto->updatedAt);
    }
}
