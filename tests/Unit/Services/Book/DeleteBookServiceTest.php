<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Book;

use App\Dto\Book\BookResponseDto;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Services\Book\DeleteBookService;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class DeleteBookServiceTest extends TestCase
{
    private BookRepositoryInterface&MockInterface $repository;
    private TransactionManagerInterface&MockInterface $transactionManager;
    private DeleteBookService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(BookRepositoryInterface::class);
        $this->transactionManager = Mockery::mock(TransactionManagerInterface::class);
        $this->transactionManager->shouldReceive('transaction')->andReturnUsing(fn (callable $callback) => $callback());
        $this->service = new DeleteBookService($this->repository, $this->transactionManager);
        Storage::fake('public');
    }

    public function test_deletes_book_and_removes_cover_image(): void
    {
        $book = new BookResponseDto(1, 't', 's', 1, null, 'd', 1.0, 1, null, 'cover.jpg', null, null, 'a', '', '');
        $this->repository->expects('getById')->with(1)->andReturn($book);
        Storage::disk('public')->put('cover.jpg', 'content');
        $this->repository->expects('delete')->with(1)->andReturn(true);

        $this->service->execute(1);

        $this->assertFalse(Storage::disk('s3')->exists('cover.jpg'));
    }

    public function test_deletes_book_without_cover_image(): void
    {
        $book = new BookResponseDto(1, 't', 's', 1, null, 'd', 1.0, 1, null, null, null, null, 'a', '', '');
        $this->repository->expects('getById')->with(1)->andReturn($book);
        $this->repository->expects('delete')->with(1)->andReturn(true);

        $this->service->execute(1);
    }
}
