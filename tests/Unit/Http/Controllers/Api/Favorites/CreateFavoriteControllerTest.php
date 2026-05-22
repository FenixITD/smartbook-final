<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Favorites;

use App\Dto\Favorite\FavoriteDto;
use App\Dto\Favorite\FavoriteResponseDto;
use App\Http\Controllers\Api\Favorites\CreateFavoriteController;
use App\Http\Requests\Favorite\FavoriteDataRequest;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class CreateFavoriteControllerTest extends TestCase
{
    private MockInterface&FavoriteRepositoryInterface $repository;
    private CreateFavoriteController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(FavoriteRepositoryInterface::class);
        $this->app->instance(FavoriteRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(CreateFavoriteController::class);
    }

    public function test_returns_201_with_created_favorite(): void
    {
        $responseDto = $this->makeResponseDto(id: 1, userId: 2, bookId: 3);

        $this->repository
            ->shouldReceive('create')
            ->once()
            ->andReturn($responseDto);

        $response = ($this->controller)($this->makeRequest(['userId' => 2, 'bookId' => 3]));

        $this->assertSame(201, $response->getStatusCode());
    }

    public function test_response_contains_created_favorite_data(): void
    {
        $responseDto = $this->makeResponseDto(id: 5, userId: 2, bookId: 3);

        $this->repository
            ->shouldReceive('create')
            ->andReturn($responseDto);

        $response = ($this->controller)($this->makeRequest(['userId' => 2, 'bookId' => 3]));
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(5, $data['id']);
        $this->assertSame(2, $data['userId']);
        $this->assertSame(3, $data['bookId']);
        $this->assertSame('2024-01-01 00:00:00', $data['createdAt']);
        $this->assertSame('2024-01-01 00:00:00', $data['updatedAt']);
    }

    public function test_passes_dto_from_request_to_repository(): void
    {
        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn (FavoriteDto $arg) => $arg->userId === 7 && $arg->bookId === 14))
            ->andReturn($this->makeResponseDto(id: 1, userId: 7, bookId: 14));

        ($this->controller)($this->makeRequest(['userId' => 7, 'bookId' => 14]));
    }

    private function makeRequest(array $data): FavoriteDataRequest
    {
        return FavoriteDataRequest::createFrom(
            Request::create('/api/favorites', 'POST', $data)
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
