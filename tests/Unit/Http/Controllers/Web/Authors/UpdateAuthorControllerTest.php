<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Authors;

use App\Dto\Author\AuthorDto;
use App\Dto\Author\AuthorResponseDto;
use App\Http\Controllers\Web\Authors\UpdateAuthorController;
use App\Http\Requests\Author\AuthorDataRequest;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class UpdateAuthorControllerTest extends TestCase
{
    private MockInterface&AuthorRepositoryInterface $repository;
    private UpdateAuthorController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(AuthorRepositoryInterface::class);
        $this->app->instance(AuthorRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(UpdateAuthorController::class);
    }

    public function test_edit_returns_view(): void
    {
        $this->repository
            ->shouldReceive('findByIdWithRelations')
            ->andReturn($this->makeResponseDto(id: 1, name: 'John Tolkien'));

        $response = $this->controller->edit(1);

        $this->assertInstanceOf(View::class, $response);
    }

    public function test_edit_returns_correct_view_name(): void
    {
        $this->repository
            ->shouldReceive('findByIdWithRelations')
            ->andReturn($this->makeResponseDto(id: 1, name: 'John Tolkien'));

        $response = $this->controller->edit(1);

        $this->assertSame('authors.edit', $response->name());
    }

    public function test_edit_view_contains_author_key(): void
    {
        $this->repository
            ->shouldReceive('findByIdWithRelations')
            ->andReturn($this->makeResponseDto(id: 1, name: 'John Tolkien'));

        $response = $this->controller->edit(1);

        $this->assertArrayHasKey('author', $response->getData());
    }

    public function test_edit_view_contains_correct_author_data(): void
    {
        $dto = $this->makeResponseDto(id: 4, name: 'John Tolkien');

        $this->repository
            ->shouldReceive('findByIdWithRelations')
            ->andReturn($dto);

        $response = $this->controller->edit(4);
        $author = $response->getData()['author'];

        $this->assertSame(4, $author->id);
        $this->assertSame('John Tolkien', $author->name);
    }

    public function test_edit_calls_repository_with_correct_id(): void
    {
        $this->repository
            ->shouldReceive('findByIdWithRelations')
            ->once()
            ->with(9)
            ->andReturn($this->makeResponseDto(id: 9, name: 'Author'));

        $this->controller->edit(9);
    }

    public function test_update_calls_repository_update_once(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->once();

        $this->controller->update($this->makeRequest(['name' => 'New Name']), 1);
    }

    public function test_update_passes_correct_id_to_repository(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with(7, Mockery::type(AuthorDto::class));

        $this->controller->update($this->makeRequest(['name' => 'New Name']), 7);
    }

    public function test_update_passes_correct_dto_to_repository(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with(
                3,
                Mockery::on(fn (AuthorDto $dto) => $dto->name === 'Updated Author'),
            );

        $this->controller->update($this->makeRequest(['name' => 'Updated Author']), 3);
    }

    public function test_update_returns_redirect_response(): void
    {
        $this->repository->shouldReceive('update');

        $response = $this->controller->update($this->makeRequest(['name' => 'New Name']), 1);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function test_update_redirects_to_authors_index(): void
    {
        $this->repository->shouldReceive('update');

        $response = $this->controller->update($this->makeRequest(['name' => 'New Name']), 1);

        $this->assertSame(route('authors.index'), $response->getTargetUrl());
    }

    private function makeRequest(array $data): AuthorDataRequest
    {
        return AuthorDataRequest::createFrom(
            Request::create('/authors/1', 'PUT', $data)
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
