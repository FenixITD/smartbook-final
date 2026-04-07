<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Dto\CartItem\CartItemDto;
use App\Dto\CartItem\CartItemFiltersDto;
use App\Dto\CartItem\CartItemResponseDto;
use App\Models\Author;
use App\Models\Book;
use App\Models\CartItem;
use App\Models\User;
use App\Repositories\Eloquent\CartItemRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartItemRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CartItemRepository $repository;

    private User $user;

    private Book $book;

    private Author $author;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CartItemRepository;
        $this->user = User::factory()->create();
        $this->author = Author::factory()->create();
        $this->book = Book::factory()->create(['author_id' => $this->author->id]);
    }

    private function makeDto(array $overrides = []): CartItemDto
    {
        return new CartItemDto(
            userId: $overrides['userId'] ?? $this->user->id,
            bookId: $overrides['bookId'] ?? $this->book->id,
            quantity: $overrides['quantity'] ?? 2,
        );
    }

    // -----------------------------------------------------------------------
    // getList
    // -----------------------------------------------------------------------

    public function test_get_list_returns_array_of_cart_item_response_dtos(): void
    {
        CartItem::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $result = $this->repository->getList(new CartItemFiltersDto);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertContainsOnlyInstancesOf(CartItemResponseDto::class, $result);
    }

    public function test_get_list_returns_empty_array_when_no_cart_items(): void
    {
        $result = $this->repository->getList(new CartItemFiltersDto);

        $this->assertSame([], $result);
    }

    public function test_get_list_respects_per_page(): void
    {
        CartItem::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $result = $this->repository->getList(new CartItemFiltersDto(perPage: 3));

        $this->assertCount(3, $result);
    }

    public function test_get_list_sorts_by_quantity_asc(): void
    {
        CartItem::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id, 'quantity' => 5]);
        $anotherBook = Book::factory()->create(['author_id' => $this->author->id]);
        CartItem::factory()->create(['user_id' => $this->user->id, 'book_id' => $anotherBook->id, 'quantity' => 1]);

        $result = $this->repository->getList(new CartItemFiltersDto(sortBy: 'quantity', sortDirection: 'asc'));

        $this->assertSame(1, $result[0]->quantity);
        $this->assertSame(5, $result[1]->quantity);
    }

    public function test_get_list_sorts_by_quantity_desc(): void
    {
        CartItem::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id, 'quantity' => 1]);
        $anotherBook = Book::factory()->create(['author_id' => $this->author->id]);
        CartItem::factory()->create(['user_id' => $this->user->id, 'book_id' => $anotherBook->id, 'quantity' => 5]);

        $result = $this->repository->getList(new CartItemFiltersDto(sortBy: 'quantity', sortDirection: 'desc'));

        $this->assertSame(5, $result[0]->quantity);
        $this->assertSame(1, $result[1]->quantity);
    }

    // -----------------------------------------------------------------------
    // getById
    // -----------------------------------------------------------------------

    public function test_get_by_id_returns_cart_item_response_dto(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'quantity' => 3,
        ]);

        $result = $this->repository->getById($cartItem->id);

        $this->assertInstanceOf(CartItemResponseDto::class, $result);
        $this->assertSame($cartItem->id, $result->id);
        $this->assertSame(3, $result->quantity);
    }

    public function test_get_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->getById(99999);

        $this->assertNull($result);
    }

    // -----------------------------------------------------------------------
    // addOrIncrement
    // -----------------------------------------------------------------------

    public function test_add_or_increment_creates_new_cart_item_when_not_exists(): void
    {
        $dto = $this->makeDto(['quantity' => 2]);

        $this->repository->addOrIncrement($dto);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'quantity' => 2,
        ]);
    }

    public function test_add_or_increment_increments_quantity_when_already_exists(): void
    {
        CartItem::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'quantity' => 3,
        ]);

        $this->repository->addOrIncrement($this->makeDto(['quantity' => 2]));

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'quantity' => 5,
        ]);
    }

    public function test_add_or_increment_does_not_create_duplicate_records(): void
    {
        $dto = $this->makeDto(['quantity' => 1]);

        $this->repository->addOrIncrement($dto);
        $this->repository->addOrIncrement($dto);

        $this->assertDatabaseCount('cart_items', 1);
    }

    // -----------------------------------------------------------------------
    // updateQuantity
    // -----------------------------------------------------------------------

    public function test_update_quantity_changes_cart_item_quantity(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'quantity' => 1,
        ]);

        $this->repository->updateQuantity($cartItem, 10);

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 10,
        ]);
    }

    // -----------------------------------------------------------------------
    // create
    // -----------------------------------------------------------------------

    public function test_create_persists_cart_item_and_returns_dto(): void
    {
        $dto = $this->makeDto(['quantity' => 4]);

        $result = $this->repository->create($dto);

        $this->assertInstanceOf(CartItemResponseDto::class, $result);
        $this->assertSame(4, $result->quantity);
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'quantity' => 4,
        ]);
    }

    public function test_create_assigns_id_to_returned_dto(): void
    {
        $result = $this->repository->create($this->makeDto());

        $this->assertIsInt($result->id);
        $this->assertGreaterThan(0, $result->id);
    }

    // -----------------------------------------------------------------------
    // update
    // -----------------------------------------------------------------------

    public function test_update_changes_cart_item_and_returns_dto(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'quantity' => 1,
        ]);

        $result = $this->repository->update($cartItem, $this->makeDto(['quantity' => 9]));

        $this->assertInstanceOf(CartItemResponseDto::class, $result);
        $this->assertSame(9, $result->quantity);
        $this->assertDatabaseHas('cart_items', ['id' => $cartItem->id, 'quantity' => 9]);
    }

    public function test_update_does_not_create_new_record(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $this->repository->update($cartItem, $this->makeDto(['quantity' => 3]));

        $this->assertDatabaseCount('cart_items', 1);
    }

    // -----------------------------------------------------------------------
    // delete
    // -----------------------------------------------------------------------

    public function test_delete_removes_cart_item_from_database(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $result = $this->repository->delete($cartItem);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    public function test_delete_returns_true_on_success(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $result = $this->repository->delete($cartItem);

        $this->assertTrue($result);
    }
}
