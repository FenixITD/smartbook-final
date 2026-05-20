<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Authors;

use App\Dto\Author\AuthorDto;
use App\Dto\Author\AuthorResponseDto;
use App\Http\Controllers\Api\Authors\CreateAuthorController;
use App\Http\Requests\Author\AuthorDataRequest;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\Request;
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

    public function test_returns_201_with_created_author(): void
    {
        $responseDto = $this->makeResponseDto(id: 1, name: 'John Tolkien');

        $this->repository
            ->shouldReceive('create')
            ->once()
            ->andReturn($responseDto);

        $response = ($this->controller)($this->makeRequest(['name' => 'John Tolkien']));

        $this->assertSame(201, $response->getStatusCode());
    }

    public function test_response_contains_created_author_data(): void
    {
        $responseDto = $this->makeResponseDto(id: 5, name: 'John Tolkien');

        $this->repository
            ->shouldReceive('create')
            ->andReturn($responseDto);

        $response = ($this->controller)($this->makeRequest(['name' => 'John Tolkien']));
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(5, $data['id']);
        $this->assertSame('John Tolkien', $data['name']);
        $this->assertSame('2024-01-01 00:00:00', $data['createdAt']);
        $this->assertSame('2024-01-01 00:00:00', $data['updatedAt']);
    }

    public function test_passes_dto_from_request_to_repository(): void
    {
        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn (AuthorDto $arg) => $arg->name === 'George Orwell'))
            ->andReturn($this->makeResponseDto(id: 2, name: 'George Orwell'));

        ($this->controller)($this->makeRequest(['name' => 'George Orwell']));
    }

    private function makeRequest(array $data): AuthorDataRequest
    {
        return AuthorDataRequest::createFrom(
            Request::create('/api/authors', 'POST', $data)
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
