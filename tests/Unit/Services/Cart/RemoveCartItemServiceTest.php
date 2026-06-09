<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Cart;

use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Services\Cart\RemoveCartItemService;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class RemoveCartItemServiceTest extends TestCase
{
    private CartItemRepositoryInterface&MockInterface $repository;
    private RemoveCartItemService $service;

    protected function setUp(): void
    {
        parent::setUp();
        config(['activitylog.enabled' => false]);
        $this->repository = Mockery::mock(CartItemRepositoryInterface::class);
        $this->service = new RemoveCartItemService($this->repository);
    }

    public function test_removes_item_from_cart(): void
    {
        Auth::shouldReceive('id')->andReturn(1);
        Auth::shouldReceive('guard->check')->andReturn(false);
        Auth::shouldReceive('guard->user')->andReturn(null);

        $this->repository->expects('deleteByUserAndBook')->with(1, 2);

        $this->service->remove(2);
    }
}
