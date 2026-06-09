<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Cart;

use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Services\Cart\GuestCartService;
use App\Services\Cart\MergeSessionCartService;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class MergeSessionCartServiceTest extends TestCase
{
    private CartItemRepositoryInterface&MockInterface $repository;
    private GuestCartService&MockInterface $guestCartService;
    private MergeSessionCartService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(CartItemRepositoryInterface::class);
        $this->guestCartService = Mockery::mock(GuestCartService::class);
        $this->service = new MergeSessionCartService($this->repository, $this->guestCartService);
    }

    public function test_execute_does_nothing_when_cart_empty(): void
    {
        $this->guestCartService->expects('getAll')->andReturn([]);

        $this->service->execute();
    }

    public function test_execute_merges_cart_and_clears_session(): void
    {
        $cart = [1 => ['book_id' => 1, 'quantity' => 2]];
        Auth::shouldReceive('id')->andReturn(1);
        $this->guestCartService->expects('getAll')->andReturn($cart);
        $this->repository->expects('bulkAddOrIncrement')->with(1, $cart);
        $this->guestCartService->expects('clear');

        $this->service->execute();
    }
}
