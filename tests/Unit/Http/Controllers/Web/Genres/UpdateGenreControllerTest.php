<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Genres;

use App\Dto\Genre\GenreDto;
use App\Dto\Genre\GenreResponseDto;
use App\Http\Controllers\Web\Genres\UpdateGenreController;
use App\Http\Requests\Genre\GenreDataRequest;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
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

    public function test_edit_returns_view_with_genre_data(): void
    {
        $dto = $this->makeResponseDto(2, 'Sci-Fi', 'sci-fi');

        $this->repository
            ->shouldReceive('findByIdWithRelations')
            ->once()
            ->with(2)
            ->andReturn($dto);

        $response = $this->controller->edit(2);

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('genres.edit', $response->name());
        $this->assertArrayHasKey('genre', $response->getData());
        $this->assertSame($dto, $response->getData()['genre']);
    }

    public function test_update_calls_repository_update_and_redirects(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with(
                4,
                Mockery::on(fn (GenreDto $dto) => $dto->name === 'Updated Name' && $dto->slug === 'updated-name')
            )
            ->andReturn($this->makeResponseDto(4, 'Updated Name', 'updated-name'));

        $response = $this->controller->update($this->makeRequest(['name' => 'Updated Name', 'slug' => 'updated-name']), 4);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('genres.index'), $response->getTargetUrl());
    }

    private function makeRequest(array $data): GenreDataRequest
    {
        return GenreDataRequest::createFrom(
            Request::create('/genres/1', 'PUT', $data)
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
