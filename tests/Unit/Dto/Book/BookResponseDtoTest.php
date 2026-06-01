<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Book;

use App\Dto\Book\BookResponseDto;
use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class BookResponseDtoTest extends TestCase
{
    public function test_from_model_creates_dto_with_full_data_and_loaded_relations(): void
    {
        $author = new Author();
        $author->id = 1;
        $author->name = 'J.R.R. Tolkien';

        $genre = new Genre();
        $genre->id = 5;
        $genre->name = 'Fantasy';
        $genre->slug = 'fantasy';
        $genre->created_at = Carbon::parse('2026-01-01 10:00:00');
        $genre->updated_at = Carbon::parse('2026-01-01 10:00:00');

        $book = new Book();
        $book->id = 10;
        $book->title = 'The Lord of the Rings';
        $book->slug = 'lotr';
        $book->author_id = 1;
        $book->description = 'Epic high-fantasy book';
        $book->price = 29.99;
        $book->stock = 100;
        $book->publish_year = 1954;
        $book->cover_image = 'lotr.jpg';
        $book->average_rating = 4.8;
        $book->ratings_count = 5000;
        $book->status = 'published';
        $book->created_at = Carbon::parse('2026-06-01 12:00:00');
        $book->updated_at = Carbon::parse('2026-06-02 12:00:00');

        $book->setRelation('author', $author);
        $book->setRelation('genres', collect([$genre]));

        $dto = BookResponseDto::fromModel($book);

        $this->assertSame(10, $dto->id);
        $this->assertSame('The Lord of the Rings', $dto->title);
        $this->assertSame('lotr', $dto->slug);
        $this->assertSame(1, $dto->authorId);
        $this->assertSame('J.R.R. Tolkien', $dto->authorName);
        $this->assertSame('Epic high-fantasy book', $dto->description);
        $this->assertSame(29.99, $dto->price);
        $this->assertSame(100, $dto->stock);
        $this->assertSame(1954, $dto->publishYear);
        $this->assertSame('lotr.jpg', $dto->coverImage);
        $this->assertSame(4.8, $dto->averageRating);
        $this->assertSame(5000, $dto->ratingsCount);
        $this->assertSame('published', $dto->status);
        $this->assertSame('2026-06-01 12:00:00', $dto->createdAt);
        $this->assertSame('2026-06-02 12:00:00', $dto->updatedAt);
        $this->assertCount(1, $dto->genres);
        $this->assertSame(5, $dto->genres[0]->id);
    }

    public function test_from_model_creates_dto_with_missing_relations_and_nulls(): void
    {
        $book = new Book();
        $book->id = 15;
        $book->title = 'Draft Book';
        $book->slug = 'draft-book';
        $book->author_id = 99;
        $book->description = 'Work in progress';
        $book->price = 0.0;
        $book->stock = 0;
        $book->publish_year = null;
        $book->cover_image = null;
        $book->average_rating = null;
        $book->ratings_count = null;
        $book->status = 'draft';
        $book->created_at = null;
        $book->updated_at = null;

        $dto = BookResponseDto::fromModel($book);

        $this->assertSame(15, $dto->id);
        $this->assertNull($dto->authorName);
        $this->assertNull($dto->publishYear);
        $this->assertNull($dto->coverImage);
        $this->assertNull($dto->averageRating);
        $this->assertNull($dto->ratingsCount);
        $this->assertSame('', $dto->createdAt);
        $this->assertSame('', $dto->updatedAt);
        $this->assertSame([], $dto->genres);
    }
}
