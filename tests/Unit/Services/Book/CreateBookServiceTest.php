<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Book;

use App\Dto\Book\BookDto;
use App\Dto\Book\BookResponseDto;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Services\Book\CreateBookService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class CreateBookServiceTest extends TestCase
{
    private BookRepositoryInterface&MockInterface $repository;
    private TransactionManagerInterface&MockInterface $transactionManager;
    private CreateBookService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(BookRepositoryInterface::class);
        $this->transactionManager = Mockery::mock(TransactionManagerInterface::class);
        $this->transactionManager->shouldReceive('transaction')->andReturnUsing(fn (callable $callback) => $callback());
        $this->service = new CreateBookService($this->repository, $this->transactionManager);
    }

    public function test_creates_book_and_syncs_genres(): void
    {
        $dto = new BookDto('t', 's', 1, 'd', '1.0', 1, null, null, 'a');
        $book = new BookResponseDto(1, 't', 's', 1, null, 'd', '1.0', 1, null, null, null, null, 'a', '', '');
        $this->repository->expects('create')->with($dto)->andReturn($book);
        $this->repository->expects('syncBookGenres')->with(1, [1, 2]);

        $this->service->execute($dto, [1, 2]);
    }
}
