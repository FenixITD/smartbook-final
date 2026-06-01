<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\CartItem;

use App\Dto\Book\BookResponseDto;
use App\Dto\CartItem\CartItemWithBookResponseDto;
use App\Models\Book;
use App\Models\CartItem;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class CartItemWithBookResponseDtoTest extends TestCase
{
    public function test_from_model_creates_dto_with_loaded_book_relation(): void
    {
        $book = new Book();
        $book->id = 99;
        $book->title = 'Test Book';
        $book->slug = 'test-book';
        $book->author_id = 1;
        $book->description = 'Desc';
        $book->price = 10.0;
        $book->stock = 5;
        $book->status = 'published';
        $book->created_at = Carbon::parse('2026-06-01 10:00:00');
        $book->updated_at = Carbon::parse('2026-06-01 10:00:00');

        $cartItem = new CartItem();
        $cartItem->id = 5;
        $cartItem->user_id = 10;
        $cartItem->book_id = 99;
        $cartItem->quantity = 2;
        $cartItem->setRelation('book', $book);

        $dto = CartItemWithBookResponseDto::fromModel($cartItem);

        $this->assertSame(5, $dto->id);
        $this->assertSame(10, $dto->userId);
        $this->assertSame(99, $dto->bookId);
        $this->assertSame(2, $dto->quantity);
        $this->assertInstanceOf(BookResponseDto::class, $dto->book);
        $this->assertSame(99, $dto->book->id);
        $this->assertSame('Test Book', $dto->book->title);
    }

    public function test_from_model_creates_dto_without_loaded_book_relation(): void
    {
        $cartItem = new CartItem();
        $cartItem->id = 7;
        $cartItem->user_id = 12;
        $cartItem->book_id = 100;
        $cartItem->quantity = 1;

        $dto = CartItemWithBookResponseDto::fromModel($cartItem);

        $this->assertSame(7, $dto->id);
        $this->assertSame(12, $dto->userId);
        $this->assertSame(100, $dto->bookId);
        $this->assertSame(1, $dto->quantity);
        $this->assertNull($dto->book);
    }

    public function test_from_guest_creates_dto_with_book(): void
    {
        $bookDto = new BookResponseDto(
            id: 50,
            title: 'Guest Book',
            slug: 'guest-book',
            authorId: 2,
            authorName: 'Author',
            description: 'Desc',
            price: 15.0,
            stock: 10,
            publishYear: 2020,
            coverImage: null,
            averageRating: null,
            ratingsCount: null,
            status: 'published',
            createdAt: '2026-06-01 10:00:00',
            updatedAt: '2026-06-01 10:00:00'
        );

        $dto = CartItemWithBookResponseDto::fromGuest(50, 4, $bookDto);

        $this->assertNull($dto->id);
        $this->assertNull($dto->userId);
        $this->assertSame(50, $dto->bookId);
        $this->assertSame(4, $dto->quantity);
        $this->assertSame($bookDto, $dto->book);
    }

    public function test_from_guest_creates_dto_without_book(): void
    {
        $dto = CartItemWithBookResponseDto::fromGuest(80, 1, null);

        $this->assertNull($dto->id);
        $this->assertNull($dto->userId);
        $this->assertSame(80, $dto->bookId);
        $this->assertSame(1, $dto->quantity);
        $this->assertNull($dto->book);
    }
}
