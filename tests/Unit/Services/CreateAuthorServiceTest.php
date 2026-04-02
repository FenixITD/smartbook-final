<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Dto\Author\AuthorDto;
use App\Dto\Author\AuthorResponseDto;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use App\Services\Author\CreateAuthorService;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class CreateAuthorServiceTest extends TestCase
{
    private AuthorRepositoryInterface&MockObject $repository;
    private CreateAuthorService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(AuthorRepositoryInterface::class);
        $this->service = new CreateAuthorService($this->repository);
    }

    public function test_execute_calls_repository_create_with_dto(): void
    {
        $dto = new AuthorDto(name: 'Leo Tolstoy');

        $responseDto = new AuthorResponseDto(
            id: 1,
            name: 'Leo Tolstoy',
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );

        $this->repository
            ->expects($this->once())
            ->method('create')
            ->with($dto)
            ->willReturn($responseDto);

        $result = $this->service->execute($dto);

        $this->assertSame($responseDto, $result);
    }

    public function test_execute_returns_author_response_dto(): void
    {
        $dto = new AuthorDto(name: 'Fyodor Dostoevsky');

        $responseDto = new AuthorResponseDto(
            id: 2,
            name: 'Fyodor Dostoevsky',
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );

        $this->repository
            ->method('create')
            ->willReturn($responseDto);

        $result = $this->service->execute($dto);

        $this->assertInstanceOf(AuthorResponseDto::class, $result);
        $this->assertSame(2, $result->id);
        $this->assertSame('Fyodor Dostoevsky', $result->name);
    }
}
