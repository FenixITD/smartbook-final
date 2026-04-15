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

class OrderItemRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private OrderItemRepository $repository;

    private Order $order;

    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new OrderItemRepository;
        $user = User::factory()->create();
        $this->order = Order::factory()->create(['user_id' => $user->id]);
        $this->book = Book::factory()->create(['author_id' => Author::factory()->create()->id]);
    }

    private function makeDto(array $overrides = []): OrderItemDto
    {
        return new OrderItemDto(
            orderId: $overrides['orderId'] ?? $this->order->id,
            bookId: $overrides['bookId'] ?? $this->book->id,
            quantity: $overrides['quantity'] ?? 2,
            priceAtPurchase: $overrides['priceAtPurchase'] ?? 29.99,
        );
    }

    // -----------------------------------------------------------------------
    // getList
    // -----------------------------------------------------------------------

    public function test_get_list_returns_array_of_order_item_response_dtos(): void
    {
        OrderItem::factory()->count(3)->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $filters = new OrderItemFiltersDto;
        $result = $this->repository->getList($filters);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertContainsOnlyInstancesOf(OrderItemResponseDto::class, $result);
    }

    public function test_get_list_returns_empty_array_when_no_order_items(): void
    {
        $filters = new OrderItemFiltersDto;
        $result = $this->repository->getList($filters);

        $this->assertSame([], $result);
    }

    public function test_get_list_respects_per_page(): void
    {
        OrderItem::factory()->count(10)->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $filters = new OrderItemFiltersDto(perPage: 4);
        $result = $this->repository->getList($filters);

        $this->assertCount(4, $result);
    }

    public function test_get_list_sorts_by_quantity_asc(): void
    {
        OrderItem::factory()->create(['quantity' => 9, 'order_id' => $this->order->id, 'book_id' => $this->book->id]);
        OrderItem::factory()->create(['quantity' => 1, 'order_id' => $this->order->id, 'book_id' => $this->book->id]);

        $filters = new OrderItemFiltersDto(sortBy: 'quantity', sortDirection: 'asc');
        $result = $this->repository->getList($filters);

        $this->assertSame(1, $result[0]->quantity);
        $this->assertSame(9, $result[1]->quantity);
    }

    public function test_get_list_sorts_by_quantity_desc(): void
    {
        OrderItem::factory()->create(['quantity' => 1, 'order_id' => $this->order->id, 'book_id' => $this->book->id]);
        OrderItem::factory()->create(['quantity' => 9, 'order_id' => $this->order->id, 'book_id' => $this->book->id]);

        $filters = new OrderItemFiltersDto(sortBy: 'quantity', sortDirection: 'desc');
        $result = $this->repository->getList($filters);

        $this->assertSame(9, $result[0]->quantity);
        $this->assertSame(1, $result[1]->quantity);
    }

    // -----------------------------------------------------------------------
    // getById
    // -----------------------------------------------------------------------

    public function test_get_by_id_returns_order_item_response_dto(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
            'quantity' => 4,
        ]);

        $result = $this->repository->getById($orderItem->id);

        $this->assertInstanceOf(OrderItemResponseDto::class, $result);
        $this->assertSame($orderItem->id, $result->id);
        $this->assertSame(4, $result->quantity);
    }

    public function test_get_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->getById(99999);

        $this->assertNull($result);
    }

    // -----------------------------------------------------------------------
    // create
    // -----------------------------------------------------------------------

    public function test_create_persists_order_item_and_returns_dto(): void
    {
        $dto = $this->makeDto(['quantity' => 5]);

        $result = $this->repository->create($dto);

        $this->assertInstanceOf(OrderItemResponseDto::class, $result);
        $this->assertSame(5, $result->quantity);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
            'quantity' => 5,
        ]);
    }

    public function test_create_assigns_id_to_returned_dto(): void
    {
        $dto = $this->makeDto();

        $result = $this->repository->create($dto);

        $this->assertIsInt($result->id);
        $this->assertGreaterThan(0, $result->id);
    }

    public function test_create_stores_all_fields_correctly(): void
    {
        $dto = $this->makeDto([
            'quantity' => 3,
            'priceAtPurchase' => 59.99,
        ]);

        $result = $this->repository->create($dto);

        $this->assertSame($this->order->id, $result->orderId);
        $this->assertSame($this->book->id, $result->bookId);
        $this->assertSame(3, $result->quantity);
        $this->assertSame(59.99, $result->priceAtPurchase);
    }

    // -----------------------------------------------------------------------
    // update
    // -----------------------------------------------------------------------

    public function test_update_changes_order_item_fields_and_returns_dto(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
            'quantity' => 1,
        ]);
        $dto = $this->makeDto(['quantity' => 8]);

        $result = $this->repository->update($orderItem->id, $dto);

        $this->assertInstanceOf(OrderItemResponseDto::class, $result);
        $this->assertSame(8, $result->quantity);
        $this->assertDatabaseHas('order_items', ['id' => $orderItem->id, 'quantity' => 8]);
    }

    public function test_update_does_not_create_new_record(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);
        $dto = $this->makeDto(['quantity' => 2]);

        $this->repository->update($orderItem->id, $dto);

        $this->assertDatabaseCount('order_items', 1);
    }

    public function test_update_throws_exception_for_nonexistent_order_item(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->update(99999, $this->makeDto());
    }

    // -----------------------------------------------------------------------
    // delete
    // -----------------------------------------------------------------------

    public function test_delete_removes_order_item_from_database(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $result = $this->repository->delete($orderItem->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('order_items', ['id' => $orderItem->id]);
    }

    public function test_delete_returns_true_on_success(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $result = $this->repository->delete($orderItem->id);

        $this->assertTrue($result);
    }

    public function test_delete_returns_false_for_nonexistent_order_item(): void
    {
        $result = $this->repository->delete(99999);

        $this->assertFalse($result);
    }
}
