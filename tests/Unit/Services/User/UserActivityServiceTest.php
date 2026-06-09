<?php

declare(strict_types=1);

namespace Tests\Unit\Services\User;

use App\Dto\ActivityLog\ActivityLogFiltersDto;
use App\Dto\ActivityLog\ActivityLogResponseDto;
use App\Dto\Book\BookResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Interfaces\ActivityLogRepositoryInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Services\User\UserActivityService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class UserActivityServiceTest extends TestCase
{
    private ActivityLogRepositoryInterface&MockInterface $activityRepository;
    private BookRepositoryInterface&MockInterface $bookRepository;
    private UserActivityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->activityRepository = Mockery::mock(ActivityLogRepositoryInterface::class);
        $this->bookRepository = Mockery::mock(BookRepositoryInterface::class);
        $this->service = new UserActivityService($this->activityRepository, $this->bookRepository);
    }

    public function test_fetch_with_books_returns_empty_books_when_no_logs(): void
    {
        $filters = new ActivityLogFiltersDto();
        $paginated = new PaginatedResponseDto([], 0, 15, 1, 1);
        $this->activityRepository->expects('getPaginated')->with($filters)->andReturn($paginated);

        $result = $this->service->fetchWithBooks($filters);

        $this->assertSame($paginated, $result[0]);
        $this->assertSame([], $result[1]);
    }

    public function test_fetch_with_books_returns_empty_books_when_no_book_ids_in_properties(): void
    {
        $filters = new ActivityLogFiltersDto();
        $log = new ActivityLogResponseDto(1, 'log', 'desc', 'type', 1, 'name', 1, ['other' => 'val'], 'date');
        $paginated = new PaginatedResponseDto([$log], 1, 15, 1, 1);
        $this->activityRepository->expects('getPaginated')->with($filters)->andReturn($paginated);

        $result = $this->service->fetchWithBooks($filters);

        $this->assertSame($paginated, $result[0]);
        $this->assertSame([], $result[1]);
    }

    public function test_fetch_with_books_extracts_book_id_from_root_properties(): void
    {
        $filters = new ActivityLogFiltersDto();
        $log = new ActivityLogResponseDto(1, 'log', 'desc', 'type', 1, 'name', 1, ['book_id' => 10], 'date');
        $paginated = new PaginatedResponseDto([$log], 1, 15, 1, 1);
        $book = new BookResponseDto(10, 't', 's', 1, 'a', 'd', 1.0, 1, null, null, null, null, 's', '', '');

        $this->activityRepository->expects('getPaginated')->with($filters)->andReturn($paginated);
        $this->bookRepository->expects('findByIdsWithAuthor')->with([10])->andReturn([$book]);

        $result = $this->service->fetchWithBooks($filters);

        $this->assertArrayHasKey(10, $result[1]);
        $this->assertSame($book, $result[1][10]);
    }

    public function test_fetch_with_books_extracts_book_id_from_attributes(): void
    {
        $filters = new ActivityLogFiltersDto();
        $log = new ActivityLogResponseDto(1, 'log', 'desc', 'type', 1, 'name', 1, ['attributes' => ['book_id' => 20]], 'date');
        $paginated = new PaginatedResponseDto([$log], 1, 15, 1, 1);
        $book = new BookResponseDto(20, 't', 's', 1, 'a', 'd', 1.0, 1, null, null, null, null, 's', '', '');

        $this->activityRepository->expects('getPaginated')->with($filters)->andReturn($paginated);
        $this->bookRepository->expects('findByIdsWithAuthor')->with([20])->andReturn([$book]);

        $result = $this->service->fetchWithBooks($filters);

        $this->assertArrayHasKey(20, $result[1]);
        $this->assertSame($book, $result[1][20]);
    }

    public function test_fetch_with_books_extracts_book_id_from_old(): void
    {
        $filters = new ActivityLogFiltersDto();
        $log = new ActivityLogResponseDto(1, 'log', 'desc', 'type', 1, 'name', 1, ['old' => ['book_id' => 30]], 'date');
        $paginated = new PaginatedResponseDto([$log], 1, 15, 1, 1);
        $book = new BookResponseDto(30, 't', 's', 1, 'a', 'd', 1.0, 1, null, null, null, null, 's', '', '');

        $this->activityRepository->expects('getPaginated')->with($filters)->andReturn($paginated);
        $this->bookRepository->expects('findByIdsWithAuthor')->with([30])->andReturn([$book]);

        $result = $this->service->fetchWithBooks($filters);

        $this->assertArrayHasKey(30, $result[1]);
        $this->assertSame($book, $result[1][30]);
    }

    public function test_fetch_with_books_removes_duplicates_and_returns_unique_books(): void
    {
        $filters = new ActivityLogFiltersDto();
        $log1 = new ActivityLogResponseDto(1, 'log', 'desc', 'type', 1, 'name', 1, ['book_id' => 40], 'date');
        $log2 = new ActivityLogResponseDto(2, 'log', 'desc', 'type', 1, 'name', 1, ['attributes' => ['book_id' => 40]], 'date');
        $paginated = new PaginatedResponseDto([$log1, $log2], 2, 15, 1, 1);
        $book = new BookResponseDto(40, 't', 's', 1, 'a', 'd', 1.0, 1, null, null, null, null, 's', '', '');

        $this->activityRepository->expects('getPaginated')->with($filters)->andReturn($paginated);
        $this->bookRepository->expects('findByIdsWithAuthor')->with([40])->andReturn([$book]);

        $result = $this->service->fetchWithBooks($filters);

        $this->assertCount(1, $result[1]);
        $this->assertArrayHasKey(40, $result[1]);
    }
}
