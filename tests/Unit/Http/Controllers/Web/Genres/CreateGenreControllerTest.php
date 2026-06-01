<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Genres;

use App\Dto\Genre\GenreDto;
use App\Dto\Genre\GenreResponseDto;
use App\Http\Controllers\Web\Genres\CreateGenreController;
use App\Http\Requests\Genre\GenreDataRequest;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
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

    public function test_create_returns_view(): void
    {
        $response = $this->controller->create();

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('genres.create', $response->name());
    }

    public function test_store_calls_repository_create_and_redirects(): void
    {
        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn (GenreDto $dto) => $dto->name === 'Fantasy' && $dto->slug === 'fantasy'))
            ->andReturn($this->makeResponseDto(1, 'Fantasy', 'fantasy'));

        $response = $this->controller->store($this->makeRequest(['name' => 'Fantasy', 'slug' => 'fantasy']));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('genres.index'), $response->getTargetUrl());
    }

    private function makeRequest(array $data): GenreDataRequest
    {
        return GenreDataRequest::createFrom(
            Request::create('/genres', 'POST', $data)
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
