<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Genres;

use App\Dto\Genre\GenreResponseDto;
use App\Http\Controllers\Api\Genres\GetGenreController;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetGenreControllerTest extends TestCase
{
    private MockInterface&GenreRepositoryInterface $repository;
    private GetGenreController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(GenreRepositoryInterface::class);
        $this->app->instance(GenreRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(GetGenreController::class);
    }

    public function test_returns_200_with_genre(): void
    {
        $this->repository
            ->shouldReceive('getById')
            ->once()
            ->with(3)
            ->andReturn($this->makeResponseDto(id: 3, name: 'Horror', slug: 'horror'));

        $response = ($this->controller)(3);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_correct_genre_data(): void
    {
        $this->repository
            ->shouldReceive('getById')
            ->andReturn($this->makeResponseDto(id: 3, name: 'Horror', slug: 'horror'));

        $response = ($this->controller)(3);
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(3, $data['id']);
        $this->assertSame('Horror', $data['name']);
        $this->assertSame('horror', $data['slug']);
        $this->assertSame('2024-01-01 00:00:00', $data['createdAt']);
        $this->assertSame('2024-01-01 00:00:00', $data['updatedAt']);
    }

    public function test_calls_repository_with_correct_id(): void
    {
        $this->repository
            ->shouldReceive('getById')
            ->once()
            ->with(42)
            ->andReturn($this->makeResponseDto(id: 42, name: 'Genre', slug: 'genre'));

        ($this->controller)(42);
    }

    private function makeResponseDto(int $id, string $name, string $slug): GenreResponseDto
    {
        return new GenreResponseDto(
            id: $id,
            name: $name,
            slug: $slug,
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }
}
