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

/**
 * @internal
 *
 * @coversNothing
 */
final class OrderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private OrderRepository $repository;

    private User $user;

    // -----------------------------------------------------------------------
    // getList
    // -----------------------------------------------------------------------

    public function testGetListReturnsArrayOfOrderResponseDtos(): void
    {
        Order::factory()->count(3)->create(['user_id' => $this->user->id]);

        $filters = new OrderFiltersDto();
        $result = $this->repository->getList($filters);

        self::assertIsArray($result);
        self::assertCount(3, $result);
        self::assertContainsOnlyInstancesOf(OrderResponseDto::class, $result);
    }

    public function testGetListReturnsEmptyArrayWhenNoOrders(): void
    {
        $filters = new OrderFiltersDto();
        $result = $this->repository->getList($filters);

        self::assertSame([], $result);
    }

    public function testGetListRespectsPerPage(): void
    {
        Order::factory()->count(10)->create(['user_id' => $this->user->id]);

        $filters = new OrderFiltersDto(perPage: 4);
        $result = $this->repository->getList($filters);

        self::assertCount(4, $result);
    }

    public function testGetListSortsByTotalAsc(): void
    {
        Order::factory()->create(['total' => 500.00, 'user_id' => $this->user->id]);
        Order::factory()->create(['total' => 10.00, 'user_id' => $this->user->id]);

        $filters = new OrderFiltersDto(sortBy: 'total', sortDirection: 'asc');
        $result = $this->repository->getList($filters);

        self::assertSame(10.00, $result[0]->total);
        self::assertSame(500.00, $result[1]->total);
    }

    public function testGetListSortsByTotalDesc(): void
    {
        Order::factory()->create(['total' => 10.00, 'user_id' => $this->user->id]);
        Order::factory()->create(['total' => 500.00, 'user_id' => $this->user->id]);

        $filters = new OrderFiltersDto(sortBy: 'total', sortDirection: 'desc');
        $result = $this->repository->getList($filters);

        self::assertSame(500.00, $result[0]->total);
        self::assertSame(10.00, $result[1]->total);
    }

    // -----------------------------------------------------------------------
    // getById
    // -----------------------------------------------------------------------

    public function testGetByIdReturnsOrderResponseDto(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id, 'status' => 'paid']);

        $result = $this->repository->getById($order->id);

        self::assertInstanceOf(OrderResponseDto::class, $result);
        self::assertSame($order->id, $result->id);
        self::assertSame('paid', $result->status);
    }

    public function testGetByIdReturnsNullWhenNotFound(): void
    {
        $result = $this->repository->getById(99999);

        self::assertNull($result);
    }

    // -----------------------------------------------------------------------
    // create
    // -----------------------------------------------------------------------

    public function testCreatePersistsOrderAndReturnsDto(): void
    {
        $dto = $this->makeDto(['status' => 'shipped']);

        $result = $this->repository->create($dto);

        self::assertInstanceOf(OrderResponseDto::class, $result);
        self::assertSame('shipped', $result->status);
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'status' => 'shipped',
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
            'total' => 249.50,
            'status' => 'delivered',
            'shippingAddress' => '456 Oxford St, London',
            'paymentMethod' => 'paypal',
        ]);

        $result = $this->repository->create($dto);

        self::assertSame(249.50, $result->total);
        self::assertSame('delivered', $result->status);
        self::assertSame('456 Oxford St, London', $result->shippingAddress);
        self::assertSame('paypal', $result->paymentMethod);
    }

    // -----------------------------------------------------------------------
    // update
    // -----------------------------------------------------------------------

    public function testUpdateChangesOrderFieldsAndReturnsDto(): void
    {
        $order = Order::factory()->create(['status' => 'pending', 'user_id' => $this->user->id]);
        $dto = $this->makeDto(['status' => 'cancelled']);

        $result = $this->repository->update($order->id, $dto);

        self::assertInstanceOf(OrderResponseDto::class, $result);
        self::assertSame('cancelled', $result->status);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
    }

    public function testUpdateDoesNotCreateNewRecord(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);
        $dto = $this->makeDto(['status' => 'paid']);

        $this->repository->update($order->id, $dto);

        $this->assertDatabaseCount('orders', 1);
    }

    public function testUpdateThrowsExceptionForNonexistentOrder(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->update(99999, $this->makeDto());
    }

    // -----------------------------------------------------------------------
    // delete
    // -----------------------------------------------------------------------

    public function testDeleteRemovesOrderFromDatabase(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $result = $this->repository->delete($order->id);

        self::assertTrue($result);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function testDeleteReturnsTrueOnSuccess(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $result = $this->repository->delete($order->id);

        self::assertTrue($result);
    }

    public function testDeleteReturnsFalseForNonexistentOrder(): void
    {
        $result = $this->repository->delete(99999);

        self::assertFalse($result);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new OrderRepository();
        $this->user = User::factory()->create();
    }

    /** @param array<string, mixed> $overrides */
    private function makeDto(array $overrides = []): OrderDto
    {
        return new OrderDto(
            userId: (int) ($overrides['userId'] ?? $this->user->id),
            total: (float) ($overrides['total'] ?? 99.99),
            status: (string) ($overrides['status'] ?? 'pending'),
            shippingAddress: (string) ($overrides['shippingAddress'] ?? '123 Main St, London'),
            paymentMethod: (string) ($overrides['paymentMethod'] ?? 'credit_card'),
        );
    }
}
