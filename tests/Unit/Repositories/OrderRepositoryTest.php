<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Dto\Order\OrderDto;
use App\Dto\Order\OrderFiltersDto;
use App\Dto\Order\OrderResponseDto;
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
        $this->repository = new OrderRepository;
        $this->user = User::factory()->create();
    }

    private function makeDto(array $overrides = []): OrderDto
    {
        return new OrderDto(
            userId: $overrides['userId'] ?? $this->user->id,
            total: $overrides['total'] ?? 99.99,
            status: $overrides['status'] ?? 'pending',
            shippingAddress: $overrides['shippingAddress'] ?? '123 Main St, London',
            paymentMethod: $overrides['paymentMethod'] ?? 'credit_card',
        );
    }

    // -----------------------------------------------------------------------
    // getList
    // -----------------------------------------------------------------------

    public function test_get_list_returns_array_of_order_response_dtos(): void
    {
        Order::factory()->count(3)->create(['user_id' => $this->user->id]);

        $filters = new OrderFiltersDto;
        $result = $this->repository->getList($filters);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertContainsOnlyInstancesOf(OrderResponseDto::class, $result);
    }

    public function test_get_list_returns_empty_array_when_no_orders(): void
    {
        $filters = new OrderFiltersDto;
        $result = $this->repository->getList($filters);

        $this->assertSame([], $result);
    }

    public function test_get_list_respects_per_page(): void
    {
        Order::factory()->count(10)->create(['user_id' => $this->user->id]);

        $filters = new OrderFiltersDto(perPage: 4);
        $result = $this->repository->getList($filters);

        $this->assertCount(4, $result);
    }

    public function test_get_list_sorts_by_total_asc(): void
    {
        Order::factory()->create(['total' => 500.00, 'user_id' => $this->user->id]);
        Order::factory()->create(['total' => 10.00, 'user_id' => $this->user->id]);

        $filters = new OrderFiltersDto(sortBy: 'total', sortDirection: 'asc');
        $result = $this->repository->getList($filters);

        $this->assertSame(10.00, $result[0]->total);
        $this->assertSame(500.00, $result[1]->total);
    }

    public function test_get_list_sorts_by_total_desc(): void
    {
        Order::factory()->create(['total' => 10.00, 'user_id' => $this->user->id]);
        Order::factory()->create(['total' => 500.00, 'user_id' => $this->user->id]);

        $filters = new OrderFiltersDto(sortBy: 'total', sortDirection: 'desc');
        $result = $this->repository->getList($filters);

        $this->assertSame(500.00, $result[0]->total);
        $this->assertSame(10.00, $result[1]->total);
    }

    // -----------------------------------------------------------------------
    // getById
    // -----------------------------------------------------------------------

    public function test_get_by_id_returns_order_response_dto(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id, 'status' => 'paid']);

        $result = $this->repository->getById($order->id);

        $this->assertInstanceOf(OrderResponseDto::class, $result);
        $this->assertSame($order->id, $result->id);
        $this->assertSame('paid', $result->status);
    }

    public function test_get_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->getById(99999);

        $this->assertNull($result);
    }

    // -----------------------------------------------------------------------
    // create
    // -----------------------------------------------------------------------

    public function test_create_persists_order_and_returns_dto(): void
    {
        $dto = $this->makeDto(['status' => 'shipped']);

        $result = $this->repository->create($dto);

        $this->assertInstanceOf(OrderResponseDto::class, $result);
        $this->assertSame('shipped', $result->status);
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'status' => 'shipped',
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
            'total' => 249.50,
            'status' => 'delivered',
            'shippingAddress' => '456 Oxford St, London',
            'paymentMethod' => 'paypal',
        ]);

        $result = $this->repository->create($dto);

        $this->assertSame(249.50, $result->total);
        $this->assertSame('delivered', $result->status);
        $this->assertSame('456 Oxford St, London', $result->shippingAddress);
        $this->assertSame('paypal', $result->paymentMethod);
    }

    // -----------------------------------------------------------------------
    // update
    // -----------------------------------------------------------------------

    public function test_update_changes_order_fields_and_returns_dto(): void
    {
        $order = Order::factory()->create(['status' => 'pending', 'user_id' => $this->user->id]);
        $dto = $this->makeDto(['status' => 'cancelled']);

        $result = $this->repository->update($order->id, $dto);

        $this->assertInstanceOf(OrderResponseDto::class, $result);
        $this->assertSame('cancelled', $result->status);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
    }

    public function test_update_does_not_create_new_record(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);
        $dto = $this->makeDto(['status' => 'paid']);

        $this->repository->update($order->id, $dto);

        $this->assertDatabaseCount('orders', 1);
    }

    public function test_update_throws_exception_for_nonexistent_order(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->update(99999, $this->makeDto());
    }

    // -----------------------------------------------------------------------
    // delete
    // -----------------------------------------------------------------------

    public function test_delete_removes_order_from_database(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $result = $this->repository->delete($order->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_delete_returns_true_on_success(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $result = $this->repository->delete($order->id);

        $this->assertTrue($result);
    }

    public function test_delete_returns_false_for_nonexistent_order(): void
    {
        $result = $this->repository->delete(99999);

        $this->assertFalse($result);
    }
}
