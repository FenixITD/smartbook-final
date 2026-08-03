<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Dto\Order\OrderDto;
use App\Dto\Order\OrderFiltersDto;
use App\Dto\Order\OrderResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Models\Order;
use App\Models\User;
use App\Repositories\Eloquent\OrderRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private OrderRepository $repository;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new OrderRepository();
        $this->user = User::factory()->create();
    }

    private function createOrder(array $attributes = []): Order
    {
        return Order::create(array_merge([
            'user_id' => $this->user->id,
            'total' => 99.99,
            'status' => 'pending',
            'shipping_address' => '123 Test St',
            'payment_method' => 'card',
        ], $attributes));
    }

    private function makeDto(array $attributes = []): OrderDto
    {
        return new OrderDto(
            userId: $attributes['user_id'] ?? $this->user->id,
            total: $attributes['total'] ?? '49.99',
            status: $attributes['status'] ?? 'pending',
            shippingAddress: $attributes['shipping_address'] ?? '456 Main St',
            paymentMethod: $attributes['payment_method'] ?? 'cash',
        );
    }

    public function test_get_list_returns_empty_array_when_no_orders(): void
    {
        $filters = new OrderFiltersDto();

        $result = $this->repository->getList($filters);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_get_list_returns_array_of_order_response_dtos(): void
    {
        $this->createOrder();

        $filters = new OrderFiltersDto();

        $result = $this->repository->getList($filters);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(OrderResponseDto::class, $result[0]);
    }

    public function test_get_list_filters_by_id(): void
    {
        $order1 = $this->createOrder(['status' => 'pending']);
        $this->createOrder(['status' => 'paid']);

        $filters = new OrderFiltersDto(id: $order1->id);

        $result = $this->repository->getList($filters);

        $this->assertCount(1, $result);
        $this->assertEquals($order1->id, $result[0]->id);
    }

    public function test_get_list_respects_sort_direction(): void
    {
        $order1 = $this->createOrder(['total' => 10.00]);
        $order2 = $this->createOrder(['total' => 20.00]);

        $filtersAsc = new OrderFiltersDto(sortBy: 'id', sortDirection: 'asc');
        $resultAsc = $this->repository->getList($filtersAsc);

        $this->assertEquals($order1->id, $resultAsc[0]->id);
        $this->assertEquals($order2->id, $resultAsc[1]->id);

        $filtersDesc = new OrderFiltersDto(sortBy: 'id', sortDirection: 'desc');
        $resultDesc = $this->repository->getList($filtersDesc);

        $this->assertEquals($order2->id, $resultDesc[0]->id);
        $this->assertEquals($order1->id, $resultDesc[1]->id);
    }

    public function test_get_list_respects_per_page(): void
    {
        foreach (range(1, 5) as $i) {
            $this->createOrder(['total' => $i * 10.0]);
        }

        $filters = new OrderFiltersDto(perPage: 2);

        $result = $this->repository->getList($filters);

        $this->assertCount(2, $result);
    }

    public function test_get_web_list_by_ids_returns_paginated_response_dto(): void
    {
        $order1 = $this->createOrder();
        $order2 = $this->createOrder();
        $ids = [$order1->id, $order2->id];

        $filters = new OrderFiltersDto();

        $result = $this->repository->getWebListByIds($ids, count($ids), $filters);

        $this->assertInstanceOf(PaginatedResponseDto::class, $result);
        $this->assertCount(2, $result->items);
        $this->assertEquals(2, $result->total);
    }

    public function test_get_web_list_by_ids_returns_empty_when_ids_not_found(): void
    {
        $filters = new OrderFiltersDto();

        $result = $this->repository->getWebListByIds([], 0, $filters);

        $this->assertCount(0, $result->items);
        $this->assertEquals(0, $result->total);
    }

    public function test_get_web_list_by_ids_preserves_given_id_order(): void
    {
        $orders = collect([$this->createOrder(), $this->createOrder(), $this->createOrder()]);
        $ids = $orders->pluck('id')->shuffle()->values()->all();

        $result = $this->repository->getWebListByIds($ids, count($ids), new OrderFiltersDto());

        $this->assertSame($ids, array_map(static fn (OrderResponseDto $order): int => $order->id, $result->items));
    }

    public function test_get_by_id_returns_order_response_dto(): void
    {
        $order = $this->createOrder();

        $result = $this->repository->getById($order->id);

        $this->assertInstanceOf(OrderResponseDto::class, $result);
        $this->assertEquals($order->id, $result->id);
        $this->assertEquals($this->user->id, $result->userId);
    }

    public function test_get_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->getById(99999);

        $this->assertNull($result);
    }

    public function test_find_by_id_with_relations_returns_order_response_dto(): void
    {
        $order = $this->createOrder();

        $result = $this->repository->findByIdWithRelations($order->id);

        $this->assertInstanceOf(OrderResponseDto::class, $result);
        $this->assertEquals($order->id, $result->id);
        $this->assertEquals($this->user->name, $result->userName);
    }

    public function test_find_by_id_with_relations_throws_exception_when_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->findByIdWithRelations(99999);
    }

    public function test_get_by_ids_returns_orders_for_given_ids(): void
    {
        $order1 = $this->createOrder();
        $order2 = $this->createOrder();
        $this->createOrder();

        $result = $this->repository->getByIds([$order1->id, $order2->id]);

        $this->assertCount(2, $result);
        $this->assertInstanceOf(OrderResponseDto::class, $result[0]);

        $resultIds = array_map(static fn (OrderResponseDto $dto) => $dto->id, $result);
        $this->assertContains($order1->id, $resultIds);
        $this->assertContains($order2->id, $resultIds);
    }

    public function test_get_by_ids_returns_empty_array_when_ids_not_found(): void
    {
        $this->createOrder();

        $result = $this->repository->getByIds([99999, 88888]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_get_by_ids_loads_user_relation(): void
    {
        $order = $this->createOrder();

        $result = $this->repository->getByIds([$order->id]);

        $this->assertCount(1, $result);
        $this->assertEquals($this->user->name, $result[0]->userName);
    }

    public function test_create_saves_order_to_database(): void
    {
        $dto = $this->makeDto();

        $result = $this->repository->create($dto);

        $this->assertInstanceOf(OrderResponseDto::class, $result);
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'status' => 'pending',
            'payment_method' => 'cash',
        ]);
    }

    public function test_create_returns_dto_with_correct_data(): void
    {
        $dto = $this->makeDto(['total' => '123.45', 'status' => 'paid']);

        $result = $this->repository->create($dto);

        $this->assertEquals($this->user->id, $result->userId);
        $this->assertSame('123.45', $result->total);
        $this->assertEquals('paid', $result->status);
        $this->assertNotEmpty($result->createdAt);
        $this->assertNotEmpty($result->updatedAt);
    }

    public function test_update_changes_order_in_database(): void
    {
        $order = $this->createOrder(['status' => 'pending']);

        $dto = $this->makeDto(['status' => 'shipped', 'total' => '200.00']);

        $result = $this->repository->update($order->id, $dto);

        $this->assertInstanceOf(OrderResponseDto::class, $result);
        $this->assertEquals('shipped', $result->status);
        $this->assertSame('200.00', $result->total);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipped',
        ]);
    }

    public function test_update_throws_exception_when_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $dto = $this->makeDto();
        $this->repository->update(99999, $dto);
    }

    public function test_delete_removes_order_from_database(): void
    {
        $order = $this->createOrder();

        $result = $this->repository->delete($order->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_delete_throws_exception_when_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->delete(99999);
    }
}
