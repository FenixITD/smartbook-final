<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Book;

use App\Dto\Book\BookDto;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Services\Book\UpdateBookService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class UpdateBookServiceTest extends TestCase
{
    private BookRepositoryInterface&MockInterface $repository;
    private TransactionManagerInterface&MockInterface $transactionManager;
    private UpdateBookService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(BookRepositoryInterface::class);
        $this->transactionManager = Mockery::mock(TransactionManagerInterface::class);
        $this->transactionManager->shouldReceive('transaction')->andReturnUsing(fn (callable $callback) => $callback());
        $this->service = new UpdateBookService($this->repository, $this->transactionManager);
    }

    public function test_updates_book_and_syncs_genres(): void
    {
        $dto = new BookDto('t', 's', 1, 'd', 1.0, 1, null, null, 'a');
        $this->repository->expects('update')->with(1, $dto)->andReturn(null);
        $this->repository->expects('syncBookGenres')->with(1, [1, 2]);

        $this->service->execute(1, $dto, [1, 2]);
    }
}
