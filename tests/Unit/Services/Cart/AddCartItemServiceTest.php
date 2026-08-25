<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Cart;

use App\Dto\Book\BookResponseDto;
use App\Dto\CartItem\CartItemDto;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Services\Cart\AddCartItemService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class AddCartItemServiceTest extends TestCase
{
    private CartItemRepositoryInterface&MockInterface $repository;
    private BookRepositoryInterface&MockInterface $bookRepository;
    private TransactionManagerInterface&MockInterface $transactionManager;
    private AddCartItemService $service;

    protected function setUp(): void
    {
        parent::setUp();
        config(['activitylog.enabled' => false]);
        $this->repository = Mockery::mock(CartItemRepositoryInterface::class);
        $this->bookRepository = Mockery::mock(BookRepositoryInterface::class);
        $this->transactionManager = Mockery::mock(TransactionManagerInterface::class);
        $this->transactionManager->shouldReceive('transaction')->andReturnUsing(fn (callable $fn) => $fn());
        $this->service = new AddCartItemService($this->repository, $this->bookRepository, $this->transactionManager);
    }

    public function test_adds_item_to_cart(): void
    {
        Auth::shouldReceive('id')->andReturn(1);

        $book = new BookResponseDto(2, 't', 's', 1, null, 'd', '10.0', 5, null, null, null, null, 'active', '', '');
        $this->bookRepository->expects('lockForUpdateByIds')->with([2])->andReturn([2 => $book]);
        $this->repository->expects('getQuantityByUserAndBook')->with(1, 2)->andReturn(0);
        $this->repository->expects('addOrIncrement')
            ->with(Mockery::on(fn (CartItemDto $dto) => $dto->userId === 1 && $dto->bookId === 2 && $dto->quantity === 3));

        $this->service->add(2, 3);
    }

    public function test_throws_when_book_not_found(): void
    {
        Auth::shouldReceive('id')->andReturn(1);

        $this->bookRepository->expects('lockForUpdateByIds')->with([999])->andReturn([]);
        $this->repository->expects('getQuantityByUserAndBook')->never();

        $this->expectException(ValidationException::class);
        $this->service->add(999, 1);
    }

    public function test_throws_when_exceeds_stock(): void
    {
        Auth::shouldReceive('id')->andReturn(1);

        $book = new BookResponseDto(2, 't', 's', 1, null, 'd', '10.0', 3, null, null, null, null, 'active', '', '');
        $this->bookRepository->expects('lockForUpdateByIds')->with([2])->andReturn([2 => $book]);
        $this->repository->expects('getQuantityByUserAndBook')->with(1, 2)->andReturn(2);

        $this->expectException(ValidationException::class);
        $this->service->add(2, 3);
    }
}
