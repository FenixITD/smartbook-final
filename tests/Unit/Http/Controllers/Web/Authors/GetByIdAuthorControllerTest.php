<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Web\Authors;

use App\Dto\Author\AuthorResponseDto;
use App\Http\Controllers\Web\Authors\GetByIdAuthorController;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetByIdAuthorControllerTest extends TestCase
{
    private MockInterface&AuthorRepositoryInterface $repository;
    private GetByIdAuthorController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(AuthorRepositoryInterface::class);
        $this->app->instance(AuthorRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(GetByIdAuthorController::class);
    }

    public function test_returns_view(): void
    {
        $this->repository
            ->shouldReceive('findByIdWithRelations')
            ->andReturn($this->makeResponseDto(id: 1, name: 'Stephen King'));

        $response = ($this->controller)(1);

        $this->assertInstanceOf(View::class, $response);
    }

    public function test_returns_correct_view_name(): void
    {
        $this->repository
            ->shouldReceive('findByIdWithRelations')
            ->andReturn($this->makeResponseDto(id: 1, name: 'Stephen King'));

        $response = ($this->controller)(1);

        $this->assertSame('authors.show', $response->name());
    }

    public function test_view_contains_author_key(): void
    {
        $this->repository
            ->shouldReceive('findByIdWithRelations')
            ->andReturn($this->makeResponseDto(id: 1, name: 'Stephen King'));

        $response = ($this->controller)(1);

        $this->assertArrayHasKey('author', $response->getData());
    }

    public function test_view_contains_correct_author_data(): void
    {
        $dto = $this->makeResponseDto(id: 3, name: 'Stephen King');

        $this->repository
            ->shouldReceive('findByIdWithRelations')
            ->andReturn($dto);

        $response = ($this->controller)(3);
        $author = $response->getData()['author'];

        $this->assertSame(3, $author->id);
        $this->assertSame('Stephen King', $author->name);
    }

    public function test_calls_repository_with_correct_id(): void
    {
        $this->repository
            ->shouldReceive('findByIdWithRelations')
            ->once()
            ->with(7)
            ->andReturn($this->makeResponseDto(id: 7, name: 'Author'));

        ($this->controller)(7);
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
