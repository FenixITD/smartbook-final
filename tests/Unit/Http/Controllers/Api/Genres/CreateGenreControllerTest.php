<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Genres;

use App\Dto\Genre\GenreDto;
use App\Dto\Genre\GenreResponseDto;
use App\Http\Controllers\Api\Genres\CreateGenreController;
use App\Http\Requests\Genre\GenreDataRequest;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class CreateGenreControllerTest extends TestCase
{
    private MockInterface&GenreRepositoryInterface $repository;
    private CreateGenreController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(GenreRepositoryInterface::class);
        $this->app->instance(GenreRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(CreateGenreController::class);
    }

    public function test_returns_201_with_created_genre(): void
    {
        $responseDto = $this->makeResponseDto(id: 1, name: 'Fantasy', slug: 'fantasy');

        $this->repository
            ->shouldReceive('create')
            ->once()
            ->andReturn($responseDto);

        $response = ($this->controller)($this->makeRequest(['name' => 'Fantasy', 'slug' => 'fantasy']));

        $this->assertSame(201, $response->getStatusCode());
    }

    public function test_response_contains_created_genre_data(): void
    {
        $responseDto = $this->makeResponseDto(id: 5, name: 'Fantasy', slug: 'fantasy');

        $this->repository
            ->shouldReceive('create')
            ->andReturn($responseDto);

        $response = ($this->controller)($this->makeRequest(['name' => 'Fantasy', 'slug' => 'fantasy']));
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(5, $data['id']);
        $this->assertSame('Fantasy', $data['name']);
        $this->assertSame('fantasy', $data['slug']);
        $this->assertSame('2024-01-01 00:00:00', $data['createdAt']);
        $this->assertSame('2024-01-01 00:00:00', $data['updatedAt']);
    }

    public function test_passes_dto_from_request_to_repository(): void
    {
        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn (GenreDto $arg) => $arg->name === 'Science Fiction' && $arg->slug === 'science-fiction'))
            ->andReturn($this->makeResponseDto(id: 2, name: 'Science Fiction', slug: 'science-fiction'));

        ($this->controller)($this->makeRequest(['name' => 'Science Fiction', 'slug' => 'science-fiction']));
    }

    private function makeRequest(array $data): GenreDataRequest
    {
        return GenreDataRequest::createFrom(
            Request::create('/api/genres', 'POST', $data)
        );
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
