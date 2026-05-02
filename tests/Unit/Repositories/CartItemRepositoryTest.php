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

/**
 * @internal
 *
 * @coversNothing
 */
final class CartItemRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CartItemRepository $repository;

    private User $user;

    private Book $book;

    private Author $author;

    // -----------------------------------------------------------------------
    // getList
    // -----------------------------------------------------------------------

    public function testGetListReturnsArrayOfCartItemResponseDtos(): void
    {
        CartItem::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $result = $this->repository->getList(new CartItemFiltersDto());

        self::assertIsArray($result);
        self::assertCount(3, $result);
        self::assertContainsOnlyInstancesOf(CartItemResponseDto::class, $result);
    }

    public function testGetListReturnsEmptyArrayWhenNoCartItems(): void
    {
        $result = $this->repository->getList(new CartItemFiltersDto());

        self::assertSame([], $result);
    }

    public function testGetListRespectsPerPage(): void
    {
        CartItem::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $result = $this->repository->getList(new CartItemFiltersDto(perPage: 3));

        self::assertCount(3, $result);
    }

    public function testGetListSortsByQuantityAsc(): void
    {
        CartItem::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id, 'quantity' => 5]);
        $anotherBook = Book::factory()->create(['author_id' => $this->author->id]);
        CartItem::factory()->create(['user_id' => $this->user->id, 'book_id' => $anotherBook->id, 'quantity' => 1]);

        $result = $this->repository->getList(new CartItemFiltersDto(sortBy: 'quantity', sortDirection: 'asc'));

        self::assertSame(1, $result[0]->quantity);
        self::assertSame(5, $result[1]->quantity);
    }

    public function testGetListSortsByQuantityDesc(): void
    {
        CartItem::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id, 'quantity' => 1]);
        $anotherBook = Book::factory()->create(['author_id' => $this->author->id]);
        CartItem::factory()->create(['user_id' => $this->user->id, 'book_id' => $anotherBook->id, 'quantity' => 5]);

        $result = $this->repository->getList(new CartItemFiltersDto(sortBy: 'quantity', sortDirection: 'desc'));

        self::assertSame(5, $result[0]->quantity);
        self::assertSame(1, $result[1]->quantity);
    }

    // -----------------------------------------------------------------------
    // getById
    // -----------------------------------------------------------------------

    public function testGetByIdReturnsCartItemResponseDto(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'quantity' => 3,
        ]);

        $result = $this->repository->getById($cartItem->id);

        self::assertInstanceOf(CartItemResponseDto::class, $result);
        self::assertSame($cartItem->id, $result->id);
        self::assertSame(3, $result->quantity);
    }

    public function testGetByIdReturnsNullWhenNotFound(): void
    {
        $result = $this->repository->getById(99999);

        self::assertNull($result);
    }

    // -----------------------------------------------------------------------
    // addOrIncrement
    // -----------------------------------------------------------------------

    public function testAddOrIncrementCreatesNewCartItemWhenNotExists(): void
    {
        $dto = $this->makeDto(['quantity' => 2]);

        $this->repository->addOrIncrement($dto);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'quantity' => 2,
        ]);
    }

    public function testAddOrIncrementIncrementsQuantityWhenAlreadyExists(): void
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

    public function testAddOrIncrementDoesNotCreateDuplicateRecords(): void
    {
        $dto = $this->makeDto(['quantity' => 1]);

        $this->repository->addOrIncrement($dto);
        $this->repository->addOrIncrement($dto);

        $this->assertDatabaseCount('cart_items', 1);
    }

    // -----------------------------------------------------------------------
    // updateQuantity
    // -----------------------------------------------------------------------

    public function testUpdateQuantityChangesCartItemQuantity(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'quantity' => 1,
        ]);

        $this->repository->updateQuantity($cartItem->id, 10);

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 10,
        ]);
    }

    // -----------------------------------------------------------------------
    // create
    // -----------------------------------------------------------------------

    public function testCreatePersistsCartItemAndReturnsDto(): void
    {
        $dto = $this->makeDto(['quantity' => 4]);

        $result = $this->repository->create($dto);

        self::assertInstanceOf(CartItemResponseDto::class, $result);
        self::assertSame(4, $result->quantity);
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'quantity' => 4,
        ]);
    }

    public function testCreateAssignsIdToReturnedDto(): void
    {
        $result = $this->repository->create($this->makeDto());

        self::assertIsInt($result->id);
        self::assertGreaterThan(0, $result->id);
    }

    // -----------------------------------------------------------------------
    // update
    // -----------------------------------------------------------------------

    public function testUpdateChangesCartItemAndReturnsDto(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'quantity' => 1,
        ]);

        $result = $this->repository->update($cartItem->id, $this->makeDto(['quantity' => 9]));

        self::assertInstanceOf(CartItemResponseDto::class, $result);
        self::assertSame(9, $result->quantity);
        $this->assertDatabaseHas('cart_items', ['id' => $cartItem->id, 'quantity' => 9]);
    }

    public function testUpdateDoesNotCreateNewRecord(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $this->repository->update($cartItem->id, $this->makeDto(['quantity' => 3]));

        $this->assertDatabaseCount('cart_items', 1);
    }

    // -----------------------------------------------------------------------
    // delete
    // -----------------------------------------------------------------------

    public function testDeleteRemovesCartItemFromDatabase(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $result = $this->repository->delete($cartItem->id);

        self::assertTrue($result);
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    public function testDeleteReturnsTrueOnSuccess(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $result = $this->repository->delete($cartItem->id);

        self::assertTrue($result);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->app->make(CartItemRepository::class);
        $this->user = User::factory()->create();
        $this->author = Author::factory()->create();
        $this->book = Book::factory()->create(['author_id' => $this->author->id]);
    }

    /** @param array<string, mixed> $overrides */
    private function makeDto(array $overrides = []): CartItemDto
    {
        return new CartItemDto(
            userId: (int) ($overrides['userId'] ?? $this->user->id),
            bookId: (int) ($overrides['bookId'] ?? $this->book->id),
            quantity: (int) ($overrides['quantity'] ?? 2),
        );
    }
}
