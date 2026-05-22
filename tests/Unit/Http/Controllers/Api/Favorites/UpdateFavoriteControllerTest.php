<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Favorites;

use App\Dto\Favorite\FavoriteDto;
use App\Dto\Favorite\FavoriteResponseDto;
use App\Http\Controllers\Api\Favorites\UpdateFavoriteController;
use App\Http\Requests\Favorite\FavoriteDataRequest;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class UpdateFavoriteControllerTest extends TestCase
{
    private MockInterface&FavoriteRepositoryInterface $repository;
    private UpdateFavoriteController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(FavoriteRepositoryInterface::class);
        $this->app->instance(FavoriteRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(UpdateFavoriteController::class);
    }

    public function test_returns_200_with_updated_favorite(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with(4, Mockery::type(FavoriteDto::class))
            ->andReturn($this->makeResponseDto(id: 4, userId: 2, bookId: 8));

        $response = ($this->controller)($this->makeRequest(['userId' => 2, 'bookId' => 8]), 4);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_updated_favorite_data(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->andReturn($this->makeResponseDto(id: 4, userId: 2, bookId: 8));

        $response = ($this->controller)($this->makeRequest(['userId' => 2, 'bookId' => 8]), 4);
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(4, $data['id']);
        $this->assertSame(2, $data['userId']);
        $this->assertSame(8, $data['bookId']);
    }

    public function test_passes_correct_id_and_dto_to_repository(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with(
                7,
                Mockery::on(fn (FavoriteDto $arg) => $arg->userId === 3 && $arg->bookId === 15),
            )
            ->andReturn($this->makeResponseDto(id: 7, userId: 3, bookId: 15));

        ($this->controller)($this->makeRequest(['userId' => 3, 'bookId' => 15]), 7);
    }

    private function makeRequest(array $data): FavoriteDataRequest
    {
        return FavoriteDataRequest::createFrom(
            Request::create('/api/favorites/1', 'PUT', $data)
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
