<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Favorites;

use App\Dto\Favorite\FavoriteResponseDto;
use App\Http\Controllers\Api\Favorites\GetFavoriteController;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetFavoriteControllerTest extends TestCase
{
    private MockInterface&FavoriteRepositoryInterface $repository;
    private GetFavoriteController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(FavoriteRepositoryInterface::class);
        $this->app->instance(FavoriteRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(GetFavoriteController::class);
    }

    public function test_returns_200_with_favorite(): void
    {
        $this->repository
            ->shouldReceive('getById')
            ->once()
            ->with(3)
            ->andReturn($this->makeResponseDto(id: 3, userId: 1, bookId: 5));

        $response = ($this->controller)(3);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_correct_favorite_data(): void
    {
        $this->repository
            ->shouldReceive('getById')
            ->andReturn($this->makeResponseDto(id: 3, userId: 1, bookId: 5));

        $response = ($this->controller)(3);
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(3, $data['id']);
        $this->assertSame(1, $data['userId']);
        $this->assertSame(5, $data['bookId']);
        $this->assertSame('2024-01-01 00:00:00', $data['createdAt']);
        $this->assertSame('2024-01-01 00:00:00', $data['updatedAt']);
    }

    public function test_calls_repository_with_correct_id(): void
    {
        $this->repository
            ->shouldReceive('getById')
            ->once()
            ->with(42)
            ->andReturn($this->makeResponseDto(id: 42, userId: 1, bookId: 5));

        ($this->controller)(42);
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
