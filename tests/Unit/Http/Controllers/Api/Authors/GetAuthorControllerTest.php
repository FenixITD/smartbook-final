<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\Authors;

use App\Dto\Author\AuthorResponseDto;
use App\Http\Controllers\Api\Authors\GetAuthorController;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetAuthorControllerTest extends TestCase
{
    private MockInterface&AuthorRepositoryInterface $repository;
    private GetAuthorController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(AuthorRepositoryInterface::class);
        $this->app->instance(AuthorRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(GetAuthorController::class);
    }

    public function test_returns_200_with_author(): void
    {
        $this->repository
            ->shouldReceive('getById')
            ->once()
            ->with(3)
            ->andReturn($this->makeResponseDto(id: 3, name: 'Stephen King'));

        $response = ($this->controller)(3);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_correct_author_data(): void
    {
        $this->repository
            ->shouldReceive('getById')
            ->andReturn($this->makeResponseDto(id: 3, name: 'Stephen King'));

        $response = ($this->controller)(3);
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(3, $data['id']);
        $this->assertSame('Stephen King', $data['name']);
        $this->assertSame('2024-01-01 00:00:00', $data['createdAt']);
        $this->assertSame('2024-01-01 00:00:00', $data['updatedAt']);
    }

    public function test_calls_repository_with_correct_id(): void
    {
        $this->repository
            ->shouldReceive('getById')
            ->once()
            ->with(42)
            ->andReturn($this->makeResponseDto(id: 42, name: 'Author'));

        ($this->controller)(42);
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
