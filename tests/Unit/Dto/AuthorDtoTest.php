<?php

declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\Author\AuthorDto;
use App\Dto\Author\AuthorFiltersDto;
use App\Dto\Author\AuthorResponseDto;
use App\Models\Author;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class AuthorDtoTest extends TestCase
{
    public function testAuthorDtoToArrayReturnsCorrectStructure(): void
    {
        $dto = new AuthorDto(name: 'Ivan Turgenev');

        $result = $dto->toArray();

        self::assertSame(['name' => 'Ivan Turgenev'], $result);
    }

    public function testAuthorDtoStoresNameCorrectly(): void
    {
        $dto = new AuthorDto(name: 'Mikhail Bulgakov');

        self::assertSame('Mikhail Bulgakov', $dto->name);
    }

    public function testAuthorFiltersDtoHasCorrectDefaults(): void
    {
        $dto = new AuthorFiltersDto();

        self::assertNull($dto->search);
        self::assertSame(15, $dto->perPage);
        self::assertSame('id', $dto->sortBy);
        self::assertSame('asc', $dto->sortDirection);
    }

    public function testAuthorFiltersDtoAcceptsCustomValues(): void
    {
        $dto = new AuthorFiltersDto(
            search: 'Tolstoy',
            perPage: 30,
            sortBy: 'name',
            sortDirection: 'desc',
        );

        self::assertSame('Tolstoy', $dto->search);
        self::assertSame(30, $dto->perPage);
        self::assertSame('name', $dto->sortBy);
        self::assertSame('desc', $dto->sortDirection);
    }

    public function testAuthorResponseDtoFromModel(): void
    {
        $author = new Author();
        $author->id = 5;
        $author->name = 'Boris Pasternak';
        $author->created_at = now()->setDateTimeFrom('2024-03-01 12:00:00');
        $author->updated_at = now()->setDateTimeFrom('2024-04-01 15:30:00');

        $dto = AuthorResponseDto::fromModel($author);

        self::assertSame(5, $dto->id);
        self::assertSame('Boris Pasternak', $dto->name);
        self::assertSame('2024-03-01 12:00:00', $dto->createdAt);
        self::assertSame('2024-04-01 15:30:00', $dto->updatedAt);
    }
}
