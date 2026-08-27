<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Dto\CartItem\CartItemDto;
use App\Dto\CartItem\CartItemFiltersDto;
use App\Dto\CartItem\CartItemResponseDto;
use App\Dto\CartItem\CartItemWithBookResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Models\Author;
use App\Models\Book;
use App\Models\CartItem;
use App\Models\User;
use App\Repositories\Eloquent\CartItemRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CartItemRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CartItemRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $activityLogger = Mockery::mock(\Spatie\Activitylog\ActivityLogger::class);
        $activityLogger->shouldReceive('useLog')->andReturnSelf();
        $activityLogger->shouldReceive('event')->andReturnSelf();
        $activityLogger->shouldReceive('performedOn')->andReturnSelf();
        $activityLogger->shouldReceive('withProperties')->andReturnSelf();
        $activityLogger->shouldReceive('log')->andReturnNull();
        $this->app->singleton(\Spatie\Activitylog\ActivityLogger::class, fn () => $activityLogger);

        $this->repository = new CartItemRepository();
    }

    private function makeUser(): User
    {
        return User::factory()->create();
    }

    private function makeBook(): Book
    {
        $author = Author::factory()->create();
        return Book::factory()->create(['author_id' => $author->id]);
    }

    private function makeCartItem(User $user, Book $book, int $quantity = 1): CartItem
    {
        return CartItem::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'quantity' => $quantity,
        ]);
    }

    public function test_getList_returns_array_of_cart_item_response_dtos(): void
    {
        $user = $this->makeUser();
        $book = $this->makeBook();
        $this->makeCartItem($user, $book);

        $filters = new CartItemFiltersDto();
        $result = $this->repository->getList($filters);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(CartItemResponseDto::class, $result[0]);
    }

    public function test_getList_filters_by_id(): void
    {
        $user = $this->makeUser();
        $book1 = $this->makeBook();
        $book2 = $this->makeBook();
        $item1 = $this->makeCartItem($user, $book1);
        $this->makeCartItem($user, $book2);

        $filters = new CartItemFiltersDto(id: $item1->id);
        $result = $this->repository->getList($filters);

        $this->assertCount(1, $result);
        $this->assertSame($item1->id, $result[0]->id);
    }

    public function test_getList_returns_empty_array_when_no_items(): void
    {
        $filters = new CartItemFiltersDto();
        $result = $this->repository->getList($filters);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function test_getById_returns_dto_when_exists(): void
    {
        $user = $this->makeUser();
        $book = $this->makeBook();
        $item = $this->makeCartItem($user, $book, 3);

        $result = $this->repository->getById($item->id);

        $this->assertInstanceOf(CartItemResponseDto::class, $result);
        $this->assertSame($item->id, $result->id);
        $this->assertSame($user->id, $result->userId);
        $this->assertSame($book->id, $result->bookId);
        $this->assertSame(3, $result->quantity);
    }

    public function test_getById_returns_null_when_not_found(): void
    {
        $result = $this->repository->getById(99999);

        $this->assertNull($result);
    }

    public function test_getTotalByUserId_returns_sum_of_price_times_quantity(): void
    {
        $user = $this->makeUser();
        $book1 = $this->makeBook();
        $book2 = $this->makeBook();

        Book::where('id', $book1->id)->update(['price' => 10.0]);
        Book::where('id', $book2->id)->update(['price' => 20.0]);

        $this->makeCartItem($user, $book1, 2);
        $this->makeCartItem($user, $book2, 3);

        $result = $this->repository->getTotalByUserId($user->id);

        $this->assertEqualsWithDelta(80.0, $result, 0.001);
    }

    public function test_getTotalByUserId_returns_zero_when_no_items(): void
    {
        $user = $this->makeUser();

        $result = $this->repository->getTotalByUserId($user->id);

        $this->assertEqualsWithDelta(0.0, $result, 0.001);
    }

    public function test_getTotalByUserId_ignores_other_users_items(): void
    {
        $user1 = $this->makeUser();
        $user2 = $this->makeUser();
        $book = $this->makeBook();

        Book::where('id', $book->id)->update(['price' => 10.0]);

        $this->makeCartItem($user1, $book, 5);
        $this->makeCartItem($user2, $book, 1);

        $result = $this->repository->getTotalByUserId($user1->id);

        $this->assertEqualsWithDelta(50.0, $result, 0.001);
    }

    public function test_getAllByUserId_returns_array_of_cart_item_with_book_response_dtos(): void
    {
        $user = $this->makeUser();
        $book = $this->makeBook();
        $this->makeCartItem($user, $book);

        $result = $this->repository->getAllByUserId($user->id);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(CartItemWithBookResponseDto::class, $result[0]);
    }

    public function test_getAllByUserId_loads_book_relation(): void
    {
        $user = $this->makeUser();
        $book = $this->makeBook();
        $this->makeCartItem($user, $book);

        $result = $this->repository->getAllByUserId($user->id);

        $this->assertNotNull($result[0]->book);
        $this->assertSame($book->id, $result[0]->book->id);
    }

    public function test_getAllByUserId_returns_empty_array_when_no_items(): void
    {
        $user = $this->makeUser();

        $result = $this->repository->getAllByUserId($user->id);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function test_deleteByUserId_removes_all_user_items(): void
    {
        $user = $this->makeUser();
        $book1 = $this->makeBook();
        $book2 = $this->makeBook();
        $this->makeCartItem($user, $book1);
        $this->makeCartItem($user, $book2);

        $this->repository->deleteByUserId($user->id);

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_deleteByUserId_does_not_remove_other_users_items(): void
    {
        $user1 = $this->makeUser();
        $user2 = $this->makeUser();
        $book = $this->makeBook();

        $this->makeCartItem($user1, $book);
        $this->makeCartItem($user2, $book);

        $this->repository->deleteByUserId($user1->id);

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseHas('cart_items', ['user_id' => $user2->id]);
    }

    public function test_addOrIncrement_creates_new_item_when_not_exists(): void
    {
        $user = $this->makeUser();
        $book = $this->makeBook();

        $dto = new CartItemDto(userId: $user->id, bookId: $book->id, quantity: 2);
        $this->repository->addOrIncrement($dto);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'quantity' => 2,
        ]);
    }

    public function test_addOrIncrement_increments_quantity_when_item_exists(): void
    {
        $user = $this->makeUser();
        $book = $this->makeBook();
        $this->makeCartItem($user, $book, 3);

        $dto = new CartItemDto(userId: $user->id, bookId: $book->id, quantity: 2);
        $this->repository->addOrIncrement($dto);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'quantity' => 5,
        ]);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_bulkAddOrIncrement_creates_items_when_not_exist(): void
    {
        $user = $this->makeUser();
        $book1 = $this->makeBook();
        $book2 = $this->makeBook();

        $items = [
            ['book_id' => $book1->id, 'quantity' => 2],
            ['book_id' => $book2->id, 'quantity' => 3],
        ];

        $this->repository->bulkAddOrIncrement($user->id, $items);

        $this->assertDatabaseHas('cart_items', ['user_id' => $user->id, 'book_id' => $book1->id]);
        $this->assertDatabaseHas('cart_items', ['user_id' => $user->id, 'book_id' => $book2->id]);
        $this->assertDatabaseCount('cart_items', 2);
    }

    public function test_bulkAddOrIncrement_does_nothing_when_empty_array(): void
    {
        $user = $this->makeUser();

        $this->repository->bulkAddOrIncrement($user->id, []);

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_create_persists_and_returns_dto(): void
    {
        $user = $this->makeUser();
        $book = $this->makeBook();

        $dto = new CartItemDto(userId: $user->id, bookId: $book->id, quantity: 4);
        $result = $this->repository->create($dto);

        $this->assertInstanceOf(CartItemResponseDto::class, $result);
        $this->assertSame($user->id, $result->userId);
        $this->assertSame($book->id, $result->bookId);
        $this->assertSame(4, $result->quantity);
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'quantity' => 4,
        ]);
    }

    public function test_update_changes_fields_and_returns_dto(): void
    {
        $user = $this->makeUser();
        $book = $this->makeBook();
        $item = $this->makeCartItem($user, $book, 1);

        $dto = new CartItemDto(userId: $user->id, bookId: $book->id, quantity: 7);
        $result = $this->repository->update($item->id, $dto);

        $this->assertInstanceOf(CartItemResponseDto::class, $result);
        $this->assertSame(7, $result->quantity);
        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 7]);
    }

    public function test_updateByUserAndBook_updates_quantity(): void
    {
        $user = $this->makeUser();
        $book = $this->makeBook();
        $this->makeCartItem($user, $book, 1);

        $this->repository->updateByUserAndBook($user->id, $book->id, 10);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'quantity' => 10,
        ]);
    }

    public function test_updateByUserAndBook_does_not_affect_other_users(): void
    {
        $user1 = $this->makeUser();
        $user2 = $this->makeUser();
        $book = $this->makeBook();

        $this->makeCartItem($user1, $book, 1);
        $this->makeCartItem($user2, $book, 1);

        $this->repository->updateByUserAndBook($user1->id, $book->id, 10);

        $this->assertDatabaseHas('cart_items', ['user_id' => $user2->id, 'quantity' => 1]);
    }

    public function test_delete_removes_item_and_returns_true(): void
    {
        $user = $this->makeUser();
        $book = $this->makeBook();
        $item = $this->makeCartItem($user, $book);

        $result = $this->repository->delete($item->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_deleteByUserAndBook_removes_correct_item(): void
    {
        $user = $this->makeUser();
        $book1 = $this->makeBook();
        $book2 = $this->makeBook();

        $this->makeCartItem($user, $book1);
        $item2 = $this->makeCartItem($user, $book2);

        $this->repository->deleteByUserAndBook($user->id, $book1->id);

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseHas('cart_items', ['id' => $item2->id]);
    }

    public function test_deleteByUserAndBook_does_not_remove_other_users_item(): void
    {
        $user1 = $this->makeUser();
        $user2 = $this->makeUser();
        $book = $this->makeBook();

        $this->makeCartItem($user1, $book);
        $this->makeCartItem($user2, $book);

        $this->repository->deleteByUserAndBook($user1->id, $book->id);

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseHas('cart_items', ['user_id' => $user2->id]);
    }
}
