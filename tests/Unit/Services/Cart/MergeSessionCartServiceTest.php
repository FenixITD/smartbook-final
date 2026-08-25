<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Cart;

use App\Dto\Book\BookResponseDto;
use App\Dto\CartItem\CartItemDto;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Services\Cart\GuestCartService;
use App\Services\Cart\MergeSessionCartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class MergeSessionCartServiceTest extends TestCase
{
    private CartItemRepositoryInterface&MockInterface $repository;
    private GuestCartService&MockInterface $guestCartService;
    private TransactionManagerInterface&MockInterface $transactionManager;
    private BookRepositoryInterface&MockInterface $bookRepository;
    private MergeSessionCartService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(CartItemRepositoryInterface::class);
        $this->guestCartService = Mockery::mock(GuestCartService::class);
        $this->transactionManager = Mockery::mock(TransactionManagerInterface::class);
        $this->bookRepository = Mockery::mock(BookRepositoryInterface::class);
        $this->service = new MergeSessionCartService(
            $this->repository,
            $this->guestCartService,
            $this->transactionManager,
            $this->bookRepository,
        );
    }

    public function test_execute_does_nothing_when_cart_empty(): void
    {
        $this->guestCartService->expects('getAll')->andReturn([]);

        $this->service->execute();
    }

    public function test_execute_merges_cart_when_all_items_new(): void
    {
        $cart = [1 => ['book_id' => 1, 'quantity' => 2]];
        Auth::shouldReceive('id')->andReturn(1);

        $lock = Mockery::mock();
        $lock->expects('get')->andReturn(true);
        $lock->expects('release');
        Cache::shouldReceive('lock')->with('merge_cart_1', 5)->andReturn($lock);

        $this->guestCartService->expects('getAll')->andReturn($cart);
        $this->transactionManager->expects('transaction')->andReturnUsing(fn (callable $fn) => $fn());
        $this->repository->expects('getAllByUserId')->with(1)->andReturn([]);

        $book = new BookResponseDto(1, 'Test Book', 'test', 1, null, 'd', '10.0', 5, null, null, null, null, 'active', '', '');
        $this->bookRepository->expects('findByIdsWithAuthor')->with([1])->andReturn([$book]);
        $this->repository->expects('create')->with(Mockery::on(fn (CartItemDto $dto) => $dto->userId === 1 && $dto->bookId === 1 && $dto->quantity === 2));
        $this->guestCartService->expects('clear');

        $this->service->execute();
    }

    public function test_execute_updates_existing_items(): void
    {
        $cart = [1 => ['book_id' => 1, 'quantity' => 3]];
        Auth::shouldReceive('id')->andReturn(1);

        $lock = Mockery::mock();
        $lock->expects('get')->andReturn(true);
        $lock->expects('release');
        Cache::shouldReceive('lock')->with('merge_cart_1', 5)->andReturn($lock);

        $existingItem = Mockery::mock();
        $existingItem->bookId = 1;
        $existingItem->quantity = 2;

        $this->guestCartService->expects('getAll')->andReturn($cart);
        $this->transactionManager->expects('transaction')->andReturnUsing(fn (callable $fn) => $fn());
        $this->repository->expects('getAllByUserId')->with(1)->andReturn([$existingItem]);

        $book = new BookResponseDto(1, 'Test Book', 'test', 1, null, 'd', '10.0', 10, null, null, null, null, 'active', '', '');
        $this->bookRepository->expects('findByIdsWithAuthor')->with([1])->andReturn([$book]);
        $this->repository->expects('updateByUserAndBook')->with(1, 1, 5);
        $this->guestCartService->expects('clear');

        $this->service->execute();
    }
}
