<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Cart;

use App\Dto\Book\BookResponseDto;
use App\Dto\CartItem\CartItemWithBookResponseDto;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Services\Cart\GuestCartService;
use App\Services\Cart\GuestCartStorageInterface;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class GuestCartServiceTest extends TestCase
{
    private BookRepositoryInterface&MockInterface $bookRepository;
    private GuestCartStorageInterface&MockInterface $storage;
    private GuestCartService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bookRepository = Mockery::mock(BookRepositoryInterface::class);
        $this->storage = Mockery::mock(GuestCartStorageInterface::class);
        $this->service = new GuestCartService($this->bookRepository, $this->storage);
    }

    public function test_get_all_returns_cart_from_storage(): void
    {
        $cart = [1 => ['book_id' => 1, 'quantity' => 2]];
        $this->storage->expects('getCart')->andReturn($cart);

        $this->assertSame($cart, $this->service->getAll());
    }

    public function test_get_items_returns_empty_array_when_cart_empty(): void
    {
        $this->storage->expects('getCart')->andReturn([]);

        $this->assertSame([], $this->service->getItems());
    }

    public function test_get_items_returns_mapped_dtos(): void
    {
        $cart = [1 => ['book_id' => 1, 'quantity' => 2]];
        $this->storage->expects('getCart')->andReturn($cart);

        $book = new BookResponseDto(1, 't', 's', 1, null, 'd', '1.0', 1, null, null, null, null, 'a', '', '');
        $this->bookRepository->expects('findByIdsWithAuthor')->with([1])->andReturn([$book]);

        $items = $this->service->getItems();

        $this->assertCount(1, $items);
        $this->assertInstanceOf(CartItemWithBookResponseDto::class, $items[0]);
        $this->assertSame(1, $items[0]->bookId);
        $this->assertSame(2, $items[0]->quantity);
        $this->assertSame($book, $items[0]->book);
    }

    public function test_get_total_returns_zero_when_cart_empty(): void
    {
        $this->storage->expects('getCart')->andReturn([]);

        $this->assertSame('0.00', $this->service->getTotal());
    }

    public function test_get_total_calculates_total_from_repository(): void
    {
        $cart = [1 => ['book_id' => 1, 'quantity' => 2]];
        $this->storage->expects('getCart')->andReturn($cart);
        $this->bookRepository->expects('getTotalByIdsAndQuantities')->with([1 => 2])->andReturn('20.00');

        $this->assertSame('20.00', $this->service->getTotal());
    }

    public function test_add_throws_on_invalid_quantity(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->add(1, 0);
    }

    public function test_add_saves_new_item_to_storage(): void
    {
        $book = new BookResponseDto(1, 't', 's', 1, null, 'd', '10.0', 5, null, null, null, null, 'active', '', '');
        $this->bookRepository->expects('getById')->with(1)->andReturn($book);
        $this->storage->expects('getCart')->andReturn([]);
        $this->storage->expects('saveCart')->with([1 => ['quantity' => 2, 'book_id' => 1]]);

        $this->service->add(1, 2);
    }

    public function test_add_increments_existing_item_in_storage(): void
    {
        $book = new BookResponseDto(1, 't', 's', 1, null, 'd', '10.0', 10, null, null, null, null, 'active', '', '');
        $this->bookRepository->expects('getById')->with(1)->andReturn($book);
        $this->storage->expects('getCart')->andReturn([1 => ['quantity' => 2, 'book_id' => 1]]);
        $this->storage->expects('saveCart')->with([1 => ['quantity' => 5, 'book_id' => 1]]);

        $this->service->add(1, 3);
    }

    public function test_add_throws_when_book_not_found(): void
    {
        $this->bookRepository->expects('getById')->with(999)->andReturn(null);

        $this->expectException(ValidationException::class);
        $this->service->add(999, 1);
    }

    public function test_add_throws_when_exceeds_stock(): void
    {
        $book = new BookResponseDto(1, 't', 's', 1, null, 'd', '10.0', 2, null, null, null, null, 'active', '', '');
        $this->bookRepository->expects('getById')->with(1)->andReturn($book);
        $this->storage->expects('getCart')->andReturn([1 => ['quantity' => 2, 'book_id' => 1]]);

        $this->expectException(ValidationException::class);
        $this->service->add(1, 3);
    }

    public function test_update_throws_on_invalid_quantity(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->update(1, 0);
    }

    public function test_update_ignores_missing_item(): void
    {
        $this->storage->expects('getCart')->andReturn([]);

        $this->service->update(1, 5);
    }

    public function test_update_saves_updated_item(): void
    {
        $book = new BookResponseDto(1, 't', 's', 1, null, 'd', '10.0', 10, null, null, null, null, 'active', '', '');
        $this->bookRepository->expects('getById')->with(1)->andReturn($book);
        $this->storage->expects('getCart')->andReturn([1 => ['quantity' => 2, 'book_id' => 1]]);
        $this->storage->expects('saveCart')->with([1 => ['quantity' => 5, 'book_id' => 1]]);

        $this->service->update(1, 5);
    }

    public function test_remove_deletes_item_from_storage(): void
    {
        $this->storage->expects('getCart')->andReturn([1 => ['quantity' => 2, 'book_id' => 1]]);
        $this->storage->expects('saveCart')->with([]);

        $this->service->remove(1);
    }

    public function test_remove_ignores_missing_item(): void
    {
        $this->storage->expects('getCart')->andReturn([]);

        $this->service->remove(1);
    }

    public function test_clear_calls_storage_clear(): void
    {
        $this->storage->expects('clear');

        $this->service->clear();
    }

    public function test_count_returns_sum_of_quantities(): void
    {
        $this->storage->expects('getCart')->andReturn([
            1 => ['quantity' => 2, 'book_id' => 1],
            2 => ['quantity' => 3, 'book_id' => 2],
        ]);

        $this->assertSame(5, $this->service->count());
    }
}
