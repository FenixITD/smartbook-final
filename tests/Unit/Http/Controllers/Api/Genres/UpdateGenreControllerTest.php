<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Genres;

use App\Dto\Genre\GenreDto;
use App\Dto\Genre\GenreResponseDto;
use App\Http\Controllers\Api\Genres\UpdateGenreController;
use App\Http\Requests\Genre\GenreDataRequest;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class UpdateGenreControllerTest extends TestCase
{
    private MockInterface&GenreRepositoryInterface $repository;
    private UpdateGenreController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(GenreRepositoryInterface::class);
        $this->app->instance(GenreRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(UpdateGenreController::class);
    }

    public function test_returns_200_with_updated_genre(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with(4, Mockery::type(GenreDto::class))
            ->andReturn($this->makeResponseDto(id: 4, name: 'Updated Name', slug: 'updated-name'));

        $response = ($this->controller)($this->makeRequest(['name' => 'Updated Name', 'slug' => 'updated-name']), 4);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_updated_genre_data(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->andReturn($this->makeResponseDto(id: 4, name: 'Updated Name', slug: 'updated-name'));

        $response = ($this->controller)($this->makeRequest(['name' => 'Updated Name', 'slug' => 'updated-name']), 4);
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(4, $data['id']);
        $this->assertSame('Updated Name', $data['name']);
        $this->assertSame('updated-name', $data['slug']);
    }

    public function test_passes_correct_id_and_dto_to_repository(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with(
                7,
                Mockery::on(fn (GenreDto $arg) => $arg->name === 'New Genre Name' && $arg->slug === 'new-genre-name'),
            )
            ->andReturn($this->makeResponseDto(id: 7, name: 'New Genre Name', slug: 'new-genre-name'));

        ($this->controller)($this->makeRequest(['name' => 'New Genre Name', 'slug' => 'new-genre-name']), 7);
    }

    private function makeRequest(array $data): GenreDataRequest
    {
        return GenreDataRequest::createFrom(
            Request::create('/api/genres/1', 'PUT', $data)
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
