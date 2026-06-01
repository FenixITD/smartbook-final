<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Genres;

use App\Dto\Genre\GenreResponseDto;
use App\Http\Controllers\Web\Genres\GetByIdGenreController;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetByIdGenreControllerTest extends TestCase
{
    private MockInterface&GenreRepositoryInterface $repository;
    private GetByIdGenreController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(GenreRepositoryInterface::class);
        $this->app->instance(GenreRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(GetByIdGenreController::class);
    }

    public function test_returns_view_with_genre_data(): void
    {
        $dto = $this->makeResponseDto(3, 'Horror', 'horror');

        $this->repository
            ->shouldReceive('findByIdWithRelations')
            ->once()
            ->with(3)
            ->andReturn($dto);

        $response = ($this->controller)(3);

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('genres.show', $response->name());
        $this->assertArrayHasKey('genre', $response->getData());
        $this->assertSame($dto, $response->getData()['genre']);
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
