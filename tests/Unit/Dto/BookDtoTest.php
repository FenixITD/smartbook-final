<?php

declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\Book\BookDto;
use App\Dto\Book\BookFiltersDto;
use App\Dto\Book\BookResponseDto;
use App\Models\Book;
use Tests\TestCase;

class BookDtoTest extends TestCase
{
    public function test_book_dto_to_array_returns_correct_structure(): void
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

        $this->assertSame([
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

    public function test_book_dto_stores_properties_correctly(): void
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

        $this->assertSame('Refactoring', $dto->title);
        $this->assertSame('refactoring', $dto->slug);
        $this->assertSame(5, $dto->authorId);
        $this->assertSame(49.99, $dto->price);
        $this->assertSame(20, $dto->stock);
        $this->assertSame(1999, $dto->publishYear);
        $this->assertSame('covers/refactoring.jpg', $dto->coverImage);
        $this->assertSame(4.8, $dto->averageRating);
        $this->assertSame(350, $dto->ratingsCount);
        $this->assertSame('active', $dto->status);
    }

    public function test_book_filters_dto_has_correct_defaults(): void
    {
        $dto = new BookFiltersDto;

        $this->assertNull($dto->search);
        $this->assertSame(15, $dto->perPage);
        $this->assertSame('id', $dto->sortBy);
        $this->assertSame('asc', $dto->sortDirection);
    }

    public function test_book_filters_dto_accepts_custom_values(): void
    {
        $dto = new BookFiltersDto(
            search: 'Clean Code',
            perPage: 30,
            sortBy: 'title',
            sortDirection: 'desc',
        );

        $this->assertSame('Clean Code', $dto->search);
        $this->assertSame(30, $dto->perPage);
        $this->assertSame('title', $dto->sortBy);
        $this->assertSame('desc', $dto->sortDirection);
    }

    public function test_book_response_dto_from_model(): void
    {
        $book = new Book;
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

        $this->assertSame(7, $dto->id);
        $this->assertSame('The Pragmatic Programmer', $dto->title);
        $this->assertSame('the-pragmatic-programmer', $dto->slug);
        $this->assertSame(2, $dto->authorId);
        $this->assertSame('Your journey to mastery.', $dto->description);
        $this->assertSame(39.99, $dto->price);
        $this->assertSame(5, $dto->stock);
        $this->assertSame(1999, $dto->publishYear);
        $this->assertSame('covers/pragmatic.jpg', $dto->coverImage);
        $this->assertSame(4.7, $dto->averageRating);
        $this->assertSame(200, $dto->ratingsCount);
        $this->assertSame('active', $dto->status);
        $this->assertSame('2024-01-01 10:00:00', $dto->createdAt);
        $this->assertSame('2024-06-01 12:00:00', $dto->updatedAt);
    }

    public function test_book_response_dto_handles_nullable_fields(): void
    {
        $book = new Book;
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

        $this->assertNull($dto->publishYear);
    }
}
