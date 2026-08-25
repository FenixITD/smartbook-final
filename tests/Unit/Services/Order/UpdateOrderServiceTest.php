<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Order;

use App\Dto\Order\OrderDto;
use App\Dto\Order\OrderResponseDto;
use App\Enums\OrderStatusEnum;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Services\Order\UpdateOrderService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

final class UpdateOrderServiceTest extends TestCase
{
    private OrderRepositoryInterface&MockInterface $repository;
    private OrderItemRepositoryInterface&MockInterface $orderItemRepository;
    private BookRepositoryInterface&MockInterface $bookRepository;
    private TransactionManagerInterface&MockInterface $transactionManager;
    private UpdateOrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(OrderRepositoryInterface::class);
        $this->orderItemRepository = Mockery::mock(OrderItemRepositoryInterface::class);
        $this->bookRepository = Mockery::mock(BookRepositoryInterface::class);
        $this->transactionManager = Mockery::mock(TransactionManagerInterface::class);
        $this->service = new UpdateOrderService(
            $this->repository,
            $this->orderItemRepository,
            $this->bookRepository,
            $this->transactionManager,
        );

        Cache::shouldReceive('lock')->andReturnSelf();
        Cache::shouldReceive('block')->andReturnUsing(fn ($timeout, $fn) => $fn());
    }

    public function test_updates_order_with_valid_transition(): void
    {
        $order = $this->makeOrderResponse('pending');
        $updated = $this->makeOrderResponse('paid');

        $this->repository->expects('getById')->with(1)->andReturn($order);
        $this->transactionManager->expects('transaction')->andReturnUsing(fn ($fn) => $fn());
        $this->repository->expects('update')->with(1, Mockery::on(fn ($dto) => $dto->status === 'paid'))->andReturn($updated);

        $result = $this->service->execute(1, $this->makeDto('paid'));

        $this->assertSame('paid', $result->status);
    }

    public function test_throws_on_invalid_transition(): void
    {
        $order = $this->makeOrderResponse('pending');

        $this->repository->expects('getById')->with(1)->andReturn($order);
        $this->transactionManager->expects('transaction')->andReturnUsing(fn ($fn) => $fn());

        $this->expectException(ValidationException::class);
        $this->service->execute(1, $this->makeDto('shipped'));
    }

    public function test_throws_when_order_not_found(): void
    {
        $this->repository->expects('getById')->with(999)->andReturn(null);
        $this->transactionManager->expects('transaction')->andReturnUsing(fn ($fn) => $fn());

        $this->expectException(NotFoundHttpException::class);
        $this->service->execute(999, $this->makeDto('paid'));
    }

    public function test_throws_when_status_is_invalid_enum(): void
    {
        $order = $this->makeOrderResponse('pending');

        $this->repository->expects('getById')->with(1)->andReturn($order);
        $this->transactionManager->expects('transaction')->andReturnUsing(fn ($fn) => $fn());

        $this->expectException(ValidationException::class);
        $this->service->execute(1, $this->makeDto('bogus'));
    }

    public function test_restores_stock_on_cancellation(): void
    {
        $order = $this->makeOrderResponse('paid');
        $updated = $this->makeOrderResponse('cancelled');

        $this->repository->expects('getById')->with(1)->andReturn($order);
        $this->transactionManager->expects('transaction')->andReturnUsing(fn ($fn) => $fn());
        $this->repository->expects('update')->andReturn($updated);
        $this->orderItemRepository->expects('getAllByOrderId')->with(1)->andReturn([
            (object) ['bookId' => 10, 'quantity' => 3],
            (object) ['bookId' => 20, 'quantity' => 2],
        ]);
        $this->bookRepository->expects('incrementStock')->with(10, 3)->once();
        $this->bookRepository->expects('incrementStock')->with(20, 2)->once();

        $this->service->execute(1, $this->makeDto('cancelled'));
    }

    public function test_does_not_restore_stock_for_same_status(): void
    {
        $order = $this->makeOrderResponse('pending');
        $updated = $this->makeOrderResponse('pending');

        $this->repository->expects('getById')->with(1)->andReturn($order);
        $this->transactionManager->expects('transaction')->andReturnUsing(fn ($fn) => $fn());
        $this->repository->expects('update')->andReturn($updated);
        $this->orderItemRepository->expects('getAllByOrderId')->never();
        $this->bookRepository->expects('incrementStock')->never();

        $this->service->execute(1, $this->makeDto('pending'));
    }

    private function makeOrderResponse(string $status): OrderResponseDto
    {
        return new OrderResponseDto(
            id: 1,
            userId: 1,
            userName: 'Test',
            total: '50.00',
            status: $status,
            shippingAddress: '123 Main St',
            paymentMethod: 'card',
            createdAt: now()->toIso8601String(),
            updatedAt: now()->toIso8601String(),
        );
    }

    private function makeDto(string $status): OrderDto
    {
        return new OrderDto(
            userId: 1,
            status: $status,
            shippingAddress: '123 Main St',
            paymentMethod: 'card',
        );
    }
}
