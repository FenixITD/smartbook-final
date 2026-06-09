<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Cart;

use App\Dto\CartItem\CartItemDto;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Services\Cart\AddCartItemService;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class AddCartItemServiceTest extends TestCase
{
    private CartItemRepositoryInterface&MockInterface $repository;
    private AddCartItemService $service;

    protected function setUp(): void
    {
        parent::setUp();
        config(['activitylog.enabled' => false]);
        $this->repository = Mockery::mock(CartItemRepositoryInterface::class);
        $this->service = new AddCartItemService($this->repository);
    }

    public function test_adds_item_to_cart(): void
    {
        Auth::shouldReceive('id')->andReturn(1);
        Auth::shouldReceive('guard->check')->andReturn(false);
        Auth::shouldReceive('guard->user')->andReturn(null);

        $this->repository->expects('addOrIncrement')
            ->with(Mockery::on(fn (CartItemDto $dto) => $dto->userId === 1 && $dto->bookId === 2 && $dto->quantity === 3));

        $this->service->add(2, 3);
    }
}
