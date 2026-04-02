<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Author;

use App\Dto\Author\AuthorDto;
use App\Dto\Author\AuthorResponseDto;
use App\Models\Author;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use App\Services\Author\UpdateAuthorService;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class UpdateAuthorServiceTest extends TestCase
{
    private AuthorRepositoryInterface&MockObject $repository;
    private UpdateAuthorService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(AuthorRepositoryInterface::class);
        $this->service = new UpdateAuthorService($this->repository);
    }

    public function test_execute_calls_repository_update_with_correct_arguments(): void
    {
        $author = new Author(['name' => 'Old Name']);
        $author->id = 1;

        $dto = new AuthorDto(name: 'New Name');

        $responseDto = new AuthorResponseDto(
            id: 1,
            name: 'New Name',
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-02 00:00:00',
        );

        $this->repository
            ->expects($this->once())
            ->method('update')
            ->with($author, $dto)
            ->willReturn($responseDto);

        $result = $this->service->execute($author, $dto);

        $this->assertSame($responseDto, $result);
    }

    public function test_execute_returns_updated_author_response_dto(): void
    {
        $author = new Author(['name' => 'Anton Chekhov']);
        $author->id = 3;

        $dto = new AuthorDto(name: 'Anton Chekhov Updated');

        $responseDto = new AuthorResponseDto(
            id: 3,
            name: 'Anton Chekhov Updated',
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-06-01 00:00:00',
        );

        $this->repository
            ->method('update')
            ->willReturn($responseDto);

        $result = $this->service->execute($author, $dto);

        $this->assertInstanceOf(AuthorResponseDto::class, $result);
        $this->assertSame('Anton Chekhov Updated', $result->name);
        $this->assertSame(3, $result->id);
    }
}
