<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Authors;

use App\Dto\Author\AuthorDto;
use App\Dto\Author\AuthorResponseDto;
use App\Http\Controllers\Api\Authors\UpdateAuthorController;
use App\Http\Requests\Author\AuthorDataRequest;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\Request;
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

    public function test_returns_200_with_updated_author(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with(4, Mockery::type(AuthorDto::class))
            ->andReturn($this->makeResponseDto(id: 4, name: 'Updated Name'));

        $response = ($this->controller)($this->makeRequest(['name' => 'Updated Name']), 4);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_updated_author_data(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->andReturn($this->makeResponseDto(id: 4, name: 'Updated Name'));

        $response = ($this->controller)($this->makeRequest(['name' => 'Updated Name']), 4);
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(4, $data['id']);
        $this->assertSame('Updated Name', $data['name']);
    }

    public function test_passes_correct_id_and_dto_to_repository(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with(
                7,
                Mockery::on(fn (AuthorDto $arg) => $arg->name === 'New Author Name'),
            )
            ->andReturn($this->makeResponseDto(id: 7, name: 'New Author Name'));

        ($this->controller)($this->makeRequest(['name' => 'New Author Name']), 7);
    }

    private function makeRequest(array $data): AuthorDataRequest
    {
        return AuthorDataRequest::createFrom(
            Request::create('/api/authors/1', 'PUT', $data)
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
