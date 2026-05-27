<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Authors;

use App\Dto\Author\AuthorDto;
use App\Dto\Author\AuthorResponseDto;
use App\Http\Controllers\Web\Authors\CreateAuthorController;
use App\Http\Requests\Author\AuthorDataRequest;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class CreateAuthorControllerTest extends TestCase
{
    private MockInterface&AuthorRepositoryInterface $repository;
    private CreateAuthorController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(AuthorRepositoryInterface::class);
        $this->app->instance(AuthorRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(CreateAuthorController::class);
    }

    public function test_create_returns_view(): void
    {
        $response = $this->controller->create();

        $this->assertInstanceOf(View::class, $response);
    }

    public function test_create_returns_correct_view_name(): void
    {
        $response = $this->controller->create();

        $this->assertSame('authors.create', $response->name());
    }

    public function test_store_calls_repository_create_once(): void
    {
        $this->repository
            ->shouldReceive('create')
            ->once();

        $this->controller->store($this->makeRequest(['name' => 'John Tolkien']));
    }

    public function test_store_passes_correct_dto_to_repository(): void
    {
        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn (AuthorDto $dto) => $dto->name === 'George Orwell'));

        $this->controller->store($this->makeRequest(['name' => 'George Orwell']));
    }

    public function test_store_returns_redirect_response(): void
    {
        $this->repository->shouldReceive('create');

        $response = $this->controller->store($this->makeRequest(['name' => 'John Tolkien']));

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function test_store_redirects_to_authors_index(): void
    {
        $this->repository->shouldReceive('create');

        $response = $this->controller->store($this->makeRequest(['name' => 'John Tolkien']));

        $this->assertSame(route('authors.index'), $response->getTargetUrl());
    }

    private function makeRequest(array $data): AuthorDataRequest
    {
        return AuthorDataRequest::createFrom(
            Request::create('/authors', 'POST', $data)
        );
    }

    private function makeResponseDto(int $id, string $name): AuthorResponseDto
    {
        return new AuthorResponseDto(
            id: $id,
            name: $name,
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }
}
