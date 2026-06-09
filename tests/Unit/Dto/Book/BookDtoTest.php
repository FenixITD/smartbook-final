<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Book;

use App\Dto\Book\BookDto;
use Tests\TestCase;

final class BookDtoTest extends TestCase
{
    public function test_book_dto_initializes_and_returns_array(): void
    {
        $dto = new BookDto(
            'Test Book',
            'test-book',
            3,
            'Test description',
            95.0,
            16,
            2012,
            'Test image',
            'draft',
        );

        $this->assertSame('Test Book', $dto->title);
        $this->assertSame('test-book', $dto->slug);
        $this->assertSame(3, $dto->authorId);
        $this->assertSame('Test description', $dto->description);
        $this->assertSame(95.0, $dto->price);
        $this->assertSame(16, $dto->stock);
        $this->assertSame(2012, $dto->publishYear);
        $this->assertSame('Test image', $dto->coverImage);
        $this->assertSame('draft', $dto->status);

        $this->assertSame([
            'title' => 'Test Book',
            'slug' => 'test-book',
            'author_id' => 3,
            'description' => 'Test description',
            'price' => 95.0,
            'stock' => 16,
            'publish_year' => 2012,
            'status' => 'draft',
            'cover_image' => 'Test image',
        ], $dto->toArray());
    }
}
