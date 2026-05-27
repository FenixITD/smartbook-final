<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Authors;

use App\Http\Controllers\Web\Authors\DeleteAuthorController;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class DeleteAuthorControllerTest extends TestCase
{
    private MockInterface&AuthorRepositoryInterface $repository;
    private DeleteAuthorController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(AuthorRepositoryInterface::class);
        $this->app->instance(AuthorRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(DeleteAuthorController::class);
    }

    public function test_calls_repository_delete_with_correct_id(): void
    {
        $this->repository
            ->shouldReceive('delete')
            ->once()
            ->with(5);

        ($this->controller)(5);
    }

    public function test_calls_repository_delete_once(): void
    {
        $this->repository
            ->shouldReceive('delete')
            ->once()
            ->with(42);

        ($this->controller)(42);
    }

    public function test_returns_redirect_response(): void
    {
        $this->repository->shouldReceive('delete');

        $response = ($this->controller)(1);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function test_redirects_to_authors_index(): void
    {
        $this->repository->shouldReceive('delete');

        $response = ($this->controller)(1);

        $this->assertSame(route('authors.index'), $response->getTargetUrl());
    }
}
