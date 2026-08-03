<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

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

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new OrderItemRepository();
    }

    private function makeOrderItem(array $attributes = []): OrderItem
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        return OrderItem::factory()->create(array_merge([
            'order_id' => $order->id,
            'book_id' => $book->id,
        ], $attributes));
    }

    public function test_get_list_returns_array_of_response_dtos(): void
    {
        $this->makeOrderItem();
        $this->makeOrderItem();

        $filters = new OrderItemFiltersDto();
        $result = $this->repository->getList($filters);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(OrderItemResponseDto::class, $result);
    }

    public function test_get_list_returns_empty_array_when_no_items(): void
    {
        $filters = new OrderItemFiltersDto();
        $result = $this->repository->getList($filters);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_get_list_filters_by_id(): void
    {
        $item1 = $this->makeOrderItem();
        $this->makeOrderItem();

        $filters = new OrderItemFiltersDto(id: $item1->id);
        $result = $this->repository->getList($filters);

        $this->assertCount(1, $result);
        $this->assertSame($item1->id, $result[0]->id);
    }

    public function test_get_list_respects_per_page(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->makeOrderItem();
        }

        $filters = new OrderItemFiltersDto(perPage: 3);
        $result = $this->repository->getList($filters);

        $this->assertCount(3, $result);
    }

    public function test_get_list_sorts_by_id_asc_by_default(): void
    {
        $item1 = $this->makeOrderItem();
        $item2 = $this->makeOrderItem();
        $item3 = $this->makeOrderItem();

        $filters = new OrderItemFiltersDto(perPage: 10);
        $result = $this->repository->getList($filters);

        $this->assertSame($item1->id, $result[0]->id);
        $this->assertSame($item2->id, $result[1]->id);
        $this->assertSame($item3->id, $result[2]->id);
    }

    public function test_get_list_sorts_desc(): void
    {
        $item1 = $this->makeOrderItem();
        $item2 = $this->makeOrderItem();

        $filters = new OrderItemFiltersDto(sortDirection: 'desc', perPage: 10);
        $result = $this->repository->getList($filters);

        $this->assertSame($item2->id, $result[0]->id);
        $this->assertSame($item1->id, $result[1]->id);
    }

    public function test_get_by_id_returns_response_dto(): void
    {
        $item = $this->makeOrderItem();

        $result = $this->repository->getById($item->id);

        $this->assertInstanceOf(OrderItemResponseDto::class, $result);
        $this->assertSame($item->id, $result->id);
        $this->assertSame($item->order_id, $result->orderId);
        $this->assertSame($item->book_id, $result->bookId);
        $this->assertSame($item->quantity, $result->quantity);
        $this->assertSame($item->price_at_purchase, $result->priceAtPurchase);
    }

    public function test_get_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->getById(99999);

        $this->assertNull($result);
    }

    public function test_create_persists_order_item_to_database(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        $dto = new OrderItemDto(
            orderId: $order->id,
            bookId: $book->id,
            quantity: 3,
            priceAtPurchase: '29.99',
        );

        $result = $this->repository->create($dto);

        $this->assertInstanceOf(OrderItemResponseDto::class, $result);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'book_id' => $book->id,
            'quantity' => 3,
            'price_at_purchase' => 29.99,
        ]);
    }

    public function test_create_returns_dto_with_correct_data(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        $dto = new OrderItemDto(
            orderId: $order->id,
            bookId: $book->id,
            quantity: 2,
            priceAtPurchase: '14.50',
        );

        $result = $this->repository->create($dto);

        $this->assertSame($order->id, $result->orderId);
        $this->assertSame($book->id, $result->bookId);
        $this->assertSame(2, $result->quantity);
        $this->assertSame('14.50', $result->priceAtPurchase);
        $this->assertNotEmpty($result->createdAt);
        $this->assertNotEmpty($result->updatedAt);
    }

    public function test_update_modifies_existing_order_item(): void
    {
        $item = $this->makeOrderItem(['quantity' => 1, 'price_at_purchase' => 10.00]);

        $dto = new OrderItemDto(
            orderId: $item->order_id,
            bookId: $item->book_id,
            quantity: 5,
            priceAtPurchase: '49.99',
        );

        $result = $this->repository->update($item->id, $dto);

        $this->assertInstanceOf(OrderItemResponseDto::class, $result);
        $this->assertSame(5, $result->quantity);
        $this->assertSame('49.99', $result->priceAtPurchase);
        $this->assertDatabaseHas('order_items', [
            'id' => $item->id,
            'quantity' => 5,
            'price_at_purchase' => 49.99,
        ]);
    }

    public function test_update_returns_null_when_not_found(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        $dto = new OrderItemDto(
            orderId: $order->id,
            bookId: $book->id,
            quantity: 1,
            priceAtPurchase: '9.99',
        );

        $this->expectException(ModelNotFoundException::class);

        $this->repository->update(99999, $dto);
    }

    public function test_delete_removes_order_item_from_database(): void
    {
        $item = $this->makeOrderItem();

        $result = $this->repository->delete($item->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('order_items', ['id' => $item->id]);
    }

    public function test_delete_returns_true_on_success(): void
    {
        $item = $this->makeOrderItem();

        $result = $this->repository->delete($item->id);

        $this->assertTrue($result);
    }

    public function test_delete_throws_when_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->delete(99999);
    }

    public function test_response_dto_has_correct_id(): void
    {
        $item = $this->makeOrderItem();

        $result = $this->repository->getById($item->id);

        $this->assertIsInt($result->id);
        $this->assertSame($item->id, $result->id);
    }

    public function test_get_list_with_no_id_filter_returns_all_items(): void
    {
        $this->makeOrderItem();
        $this->makeOrderItem();
        $this->makeOrderItem();

        $filters = new OrderItemFiltersDto(id: null, perPage: 100);
        $result = $this->repository->getList($filters);

        $this->assertCount(3, $result);
    }
}
