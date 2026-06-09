<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Favorite;

use App\Dto\Favorite\FavoriteFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use App\Services\Favorite\FavoriteService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class FavoriteServiceTest extends TestCase
{
    private FavoriteRepositoryInterface&MockInterface $favoriteRepository;
    private BookRepositoryInterface&MockInterface $bookRepository;
    private FavoriteService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->favoriteRepository = Mockery::mock(FavoriteRepositoryInterface::class);
        $this->bookRepository = Mockery::mock(BookRepositoryInterface::class);
        $this->service = new FavoriteService($this->favoriteRepository, $this->bookRepository);
    }

    public function test_get_books_by_user_returns_null_when_no_favorites(): void
    {
        $filters = new FavoriteFiltersDto(null, null, 15);
        $this->favoriteRepository->expects('getBookIdsByUser')->with(1)->andReturn([]);

        $result = $this->service->getBooksByUser(1, $filters);

        $this->assertNull($result);
    }

    public function test_get_books_by_user_returns_paginated_response(): void
    {
        $filters = new FavoriteFiltersDto(null, null, 10);
        $paginated = new PaginatedResponseDto([], 0, 10, 1, 1);

        $this->favoriteRepository->expects('getBookIdsByUser')->with(2)->andReturn([5, 6]);
        $this->bookRepository->expects('getByIdsWithAuthor')->with([5, 6], 10)->andReturn($paginated);

        $result = $this->service->getBooksByUser(2, $filters);

        $this->assertSame($paginated, $result);
    }
}
