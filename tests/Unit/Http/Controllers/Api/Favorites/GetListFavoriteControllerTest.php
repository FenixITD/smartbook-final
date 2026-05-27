<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Favorites;

use App\Dto\Favorite\FavoriteFiltersDto;
use App\Dto\Favorite\FavoriteResponseDto;
use App\Http\Controllers\Api\Favorites\GetListFavoriteController;
use App\Http\Requests\Favorite\FavoriteListRequest;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetListFavoriteControllerTest extends TestCase
{
    private MockInterface&FavoriteRepositoryInterface $repository;
    private GetListFavoriteController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(FavoriteRepositoryInterface::class);
        $this->app->instance(FavoriteRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(GetListFavoriteController::class);
    }

    public function test_returns_200_with_favorites_list(): void
    {
        $this->repository
            ->shouldReceive('getList')
            ->once()
            ->andReturn([
                $this->makeResponseDto(1, 1, 10),
                $this->makeResponseDto(2, 2, 11),
            ]);

        $response = ($this->controller)($this->makeRequest());

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_all_favorites(): void
    {
        $this->repository
            ->shouldReceive('getList')
            ->andReturn([
                $this->makeResponseDto(1, 1, 10),
                $this->makeResponseDto(2, 2, 11),
                $this->makeResponseDto(3, 3, 12),
            ]);

        $response = ($this->controller)($this->makeRequest());
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertCount(3, $data);
        $this->assertSame(1, $data[0]['id']);
        $this->assertSame(1, $data[0]['userId']);
        $this->assertSame(10, $data[0]['bookId']);
        $this->assertSame(2, $data[1]['id']);
        $this->assertSame(3, $data[2]['id']);
    }

    public function test_returns_empty_data_array_when_no_favorites(): void
    {
        $this->repository
            ->shouldReceive('getList')
            ->andReturn([]);

        $response = ($this->controller)($this->makeRequest());
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([], $data);
    }

    public function test_passes_filters_dto_from_request_to_repository(): void
    {
        $this->repository
            ->shouldReceive('getList')
            ->once()
            ->with(Mockery::on(function (FavoriteFiltersDto $arg) {
                return $arg->search === 'test'
                    && $arg->perPage === 10
                    && $arg->sortBy === 'bookId'
                    && $arg->sortDirection === 'desc';
            }))
            ->andReturn([]);

        ($this->controller)($this->makeRequest([
            'search' => 'test',
            'perPage' => 10,
            'sortBy' => 'bookId',
            'sortDirection' => 'desc',
        ]));
    }

    public function test_uses_default_filters_when_no_query_params(): void
    {
        $this->repository
            ->shouldReceive('getList')
            ->once()
            ->with(Mockery::on(function (FavoriteFiltersDto $arg) {
                return $arg->search === null
                    && $arg->perPage === 15
                    && $arg->sortBy === 'id'
                    && $arg->sortDirection === 'asc';
            }))
            ->andReturn([]);

        ($this->controller)($this->makeRequest());
    }

    private function makeRequest(array $params = []): FavoriteListRequest
    {
        return FavoriteListRequest::createFrom(
            Request::create('/api/favorites', 'GET', $params)
        );
    }

    private function makeResponseDto(int $id, int $userId, int $bookId): FavoriteResponseDto
    {
        return new FavoriteResponseDto(
            id: $id,
            userId: $userId,
            bookId: $bookId,
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }
}
