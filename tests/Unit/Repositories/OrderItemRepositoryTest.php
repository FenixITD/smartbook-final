<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Dto\OrderItem\OrderItemDto;
use App\Dto\OrderItem\OrderItemFiltersDto;
use App\Dto\OrderItem\OrderItemResponseDto;
use App\Models\Author;
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Repositories\Eloquent\OrderItemRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class OrderItemRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private OrderItemRepository $repository;

    private Order $order;

    private Book $book;

    // -----------------------------------------------------------------------
    // getList
    // -----------------------------------------------------------------------

    public function testGetListReturnsArrayOfOrderItemResponseDtos(): void
    {
        OrderItem::factory()->count(3)->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $filters = new OrderItemFiltersDto();
        $result = $this->repository->getList($filters);

        self::assertIsArray($result);
        self::assertCount(3, $result);
        self::assertContainsOnlyInstancesOf(OrderItemResponseDto::class, $result);
    }

    public function testGetListReturnsEmptyArrayWhenNoOrderItems(): void
    {
        $filters = new OrderItemFiltersDto();
        $result = $this->repository->getList($filters);

        self::assertSame([], $result);
    }

    public function testGetListRespectsPerPage(): void
    {
        OrderItem::factory()->count(10)->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $filters = new OrderItemFiltersDto(perPage: 4);
        $result = $this->repository->getList($filters);

        self::assertCount(4, $result);
    }

    public function testGetListSortsByQuantityAsc(): void
    {
        OrderItem::factory()->create(['quantity' => 9, 'order_id' => $this->order->id, 'book_id' => $this->book->id]);
        OrderItem::factory()->create(['quantity' => 1, 'order_id' => $this->order->id, 'book_id' => $this->book->id]);

        $filters = new OrderItemFiltersDto(sortBy: 'quantity', sortDirection: 'asc');
        $result = $this->repository->getList($filters);

        self::assertSame(1, $result[0]->quantity);
        self::assertSame(9, $result[1]->quantity);
    }

    public function testGetListSortsByQuantityDesc(): void
    {
        OrderItem::factory()->create(['quantity' => 1, 'order_id' => $this->order->id, 'book_id' => $this->book->id]);
        OrderItem::factory()->create(['quantity' => 9, 'order_id' => $this->order->id, 'book_id' => $this->book->id]);

        $filters = new OrderItemFiltersDto(sortBy: 'quantity', sortDirection: 'desc');
        $result = $this->repository->getList($filters);

        self::assertSame(9, $result[0]->quantity);
        self::assertSame(1, $result[1]->quantity);
    }

    // -----------------------------------------------------------------------
    // getById
    // -----------------------------------------------------------------------

    public function testGetByIdReturnsOrderItemResponseDto(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
            'quantity' => 4,
        ]);

        $result = $this->repository->getById($orderItem->id);

        self::assertInstanceOf(OrderItemResponseDto::class, $result);
        self::assertSame($orderItem->id, $result->id);
        self::assertSame(4, $result->quantity);
    }

    public function testGetByIdReturnsNullWhenNotFound(): void
    {
        $result = $this->repository->getById(99999);

        self::assertNull($result);
    }

    // -----------------------------------------------------------------------
    // create
    // -----------------------------------------------------------------------

    public function testCreatePersistsOrderItemAndReturnsDto(): void
    {
        $dto = $this->makeDto(['quantity' => 5]);

        $result = $this->repository->create($dto);

        self::assertInstanceOf(OrderItemResponseDto::class, $result);
        self::assertSame(5, $result->quantity);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
            'quantity' => 5,
        ]);
    }

    public function testCreateAssignsIdToReturnedDto(): void
    {
        $dto = $this->makeDto();

        $result = $this->repository->create($dto);

        self::assertIsInt($result->id);
        self::assertGreaterThan(0, $result->id);
    }

    public function testCreateStoresAllFieldsCorrectly(): void
    {
        $dto = $this->makeDto([
            'quantity' => 3,
            'priceAtPurchase' => 59.99,
        ]);

        $result = $this->repository->create($dto);

        self::assertSame($this->order->id, $result->orderId);
        self::assertSame($this->book->id, $result->bookId);
        self::assertSame(3, $result->quantity);
        self::assertSame(59.99, $result->priceAtPurchase);
    }

    // -----------------------------------------------------------------------
    // update
    // -----------------------------------------------------------------------

    public function testUpdateChangesOrderItemFieldsAndReturnsDto(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
            'quantity' => 1,
        ]);
        $dto = $this->makeDto(['quantity' => 8]);

        $result = $this->repository->update($orderItem->id, $dto);

        self::assertInstanceOf(OrderItemResponseDto::class, $result);
        self::assertSame(8, $result->quantity);
        $this->assertDatabaseHas('order_items', ['id' => $orderItem->id, 'quantity' => 8]);
    }

    public function testUpdateDoesNotCreateNewRecord(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);
        $dto = $this->makeDto(['quantity' => 2]);

        $this->repository->update($orderItem->id, $dto);

        $this->assertDatabaseCount('order_items', 1);
    }

    public function testUpdateThrowsExceptionForNonexistentOrderItem(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->update(99999, $this->makeDto());
    }

    // -----------------------------------------------------------------------
    // delete
    // -----------------------------------------------------------------------

    public function testDeleteRemovesOrderItemFromDatabase(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $result = $this->repository->delete($orderItem->id);

        self::assertTrue($result);
        $this->assertDatabaseMissing('order_items', ['id' => $orderItem->id]);
    }

    public function testDeleteReturnsTrueOnSuccess(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $result = $this->repository->delete($orderItem->id);

        self::assertTrue($result);
    }

    public function testDeleteReturnsFalseForNonexistentOrderItem(): void
    {
        $result = $this->repository->delete(99999);

        self::assertFalse($result);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new OrderItemRepository();
        $user = User::factory()->create();
        $this->order = Order::factory()->create(['user_id' => $user->id]);
        $this->book = Book::factory()->create(['author_id' => Author::factory()->create()->id]);
    }

    /** @param array<string, mixed> $overrides */
    private function makeDto(array $overrides = []): OrderItemDto
    {
        return new OrderItemDto(
            orderId: (int) ($overrides['orderId'] ?? $this->order->id),
            bookId: (int) ($overrides['bookId'] ?? $this->book->id),
            quantity: (int) ($overrides['quantity'] ?? 2),
            priceAtPurchase: (float) ($overrides['priceAtPurchase'] ?? 29.99),
        );
    }
}
