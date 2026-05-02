<?php

declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\Book\BookDto;
use App\Dto\Book\BookFiltersDto;
use App\Dto\Book\BookResponseDto;
use App\Models\Book;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class BookDtoTest extends TestCase
{
    public function testBookDtoToArrayReturnsCorrectStructure(): void
    {
        $dto = new BookDto(
            title: 'Clean Code',
            slug: 'clean-code',
            authorId: 1,
            description: 'A book about writing clean code.',
            price: 29.99,
            stock: 10,
            publishYear: 2008,
            coverImage: null,
            averageRating: null,
            ratingsCount: null,
            status: 'active',
        );

        $result = $dto->toArray();

        self::assertSame([
            'title' => 'Clean Code',
            'slug' => 'clean-code',
            'author_id' => 1,
            'description' => 'A book about writing clean code.',
            'price' => 29.99,
            'stock' => 10,
            'publish_year' => 2008,
            'cover_image' => null,
            'average_rating' => null,
            'ratings_count' => null,
            'status' => 'active',
        ], $result);
    }

    public function testBookDtoStoresPropertiesCorrectly(): void
    {
        $dto = new BookDto(
            title: 'Refactoring',
            slug: 'refactoring',
            authorId: 5,
            description: 'Improving the design of existing code.',
            price: 49.99,
            stock: 20,
            publishYear: 1999,
            coverImage: 'covers/refactoring.jpg',
            averageRating: 4.8,
            ratingsCount: 350,
            status: 'active',
        );

        self::assertSame('Refactoring', $dto->title);
        self::assertSame('refactoring', $dto->slug);
        self::assertSame(5, $dto->authorId);
        self::assertSame(49.99, $dto->price);
        self::assertSame(20, $dto->stock);
        self::assertSame(1999, $dto->publishYear);
        self::assertSame('covers/refactoring.jpg', $dto->coverImage);
        self::assertSame(4.8, $dto->averageRating);
        self::assertSame(350, $dto->ratingsCount);
        self::assertSame('active', $dto->status);
    }

    public function testBookFiltersDtoHasCorrectDefaults(): void
    {
        $dto = new BookFiltersDto();

        self::assertNull($dto->search);
        self::assertSame(15, $dto->perPage);
        self::assertSame('id', $dto->sortBy);
        self::assertSame('asc', $dto->sortDirection);
    }

    public function testBookFiltersDtoAcceptsCustomValues(): void
    {
        $dto = new BookFiltersDto(
            search: 'Clean Code',
            perPage: 30,
            sortBy: 'title',
            sortDirection: 'desc',
        );

        self::assertSame('Clean Code', $dto->search);
        self::assertSame(30, $dto->perPage);
        self::assertSame('title', $dto->sortBy);
        self::assertSame('desc', $dto->sortDirection);
    }

    public function testBookResponseDtoFromModel(): void
    {
        $book = new Book();
        $book->id = 7;
        $book->title = 'The Pragmatic Programmer';
        $book->slug = 'the-pragmatic-programmer';
        $book->author_id = 2;
        $book->description = 'Your journey to mastery.';
        $book->price = 39.99;
        $book->stock = 5;
        $book->publish_year = 1999;
        $book->cover_image = 'covers/pragmatic.jpg';
        $book->average_rating = 4.7;
        $book->ratings_count = 200;
        $book->status = 'active';
        $book->created_at = now()->setDateTimeFrom('2024-01-01 10:00:00');
        $book->updated_at = now()->setDateTimeFrom('2024-06-01 12:00:00');

        $dto = BookResponseDto::fromModel($book);

        self::assertSame(7, $dto->id);
        self::assertSame('The Pragmatic Programmer', $dto->title);
        self::assertSame('the-pragmatic-programmer', $dto->slug);
        self::assertSame(2, $dto->authorId);
        self::assertSame('Your journey to mastery.', $dto->description);
        self::assertSame(39.99, $dto->price);
        self::assertSame(5, $dto->stock);
        self::assertSame(1999, $dto->publishYear);
        self::assertSame('covers/pragmatic.jpg', $dto->coverImage);
        self::assertSame(4.7, $dto->averageRating);
        self::assertSame(200, $dto->ratingsCount);
        self::assertSame('active', $dto->status);
        self::assertSame('2024-01-01 10:00:00', $dto->createdAt);
        self::assertSame('2024-06-01 12:00:00', $dto->updatedAt);
    }

    public function testBookResponseDtoHandlesNullableFields(): void
    {
        $book = new Book();
        $book->id = 1;
        $book->title = 'Minimal Book';
        $book->slug = 'minimal-book';
        $book->author_id = 1;
        $book->description = 'Short description.';
        $book->price = 9.99;
        $book->stock = 0;
        $book->publish_year = null;
        $book->cover_image = null;
        $book->average_rating = 0;
        $book->ratings_count = 0;
        $book->status = 'draft';
        $book->created_at = now();
        $book->updated_at = now();

        $dto = BookResponseDto::fromModel($book);

        self::assertNull($dto->publishYear);
    }
}
