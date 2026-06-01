<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Genres;

use App\Http\Controllers\Web\Genres\DeleteGenreController;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class DeleteGenreControllerTest extends TestCase
{
    private MockInterface&GenreRepositoryInterface $repository;
    private DeleteGenreController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(GenreRepositoryInterface::class);
        $this->app->instance(GenreRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(DeleteGenreController::class);
    }

    public function test_calls_repository_delete_and_redirects(): void
    {
        $this->repository
            ->shouldReceive('delete')
            ->once()
            ->with(5)
            ->andReturn(true);

        $response = ($this->controller)(5);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('genres.index'), $response->getTargetUrl());
    }
}
