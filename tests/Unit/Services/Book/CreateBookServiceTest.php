<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Book;

use App\Dto\Book\BookDto;
use App\Dto\Book\BookResponseDto;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Services\Book\CreateBookService;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class CreateBookServiceTest extends TestCase
{
    private BookRepositoryInterface&MockObject $repository;
    private CreateBookService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(BookRepositoryInterface::class);
        $this->service = new CreateBookService($this->repository);
    }

    private function makeDto(string $title = 'Clean Code'): BookDto
    {
        return new BookDto(
            title: $title,
            slug: 'clean-code',
            authorId: 1,
            description: 'A book about writing clean code.',
            price: 29.99,
            stock: 10,
            publishYear: 2008,
            coverImage: null,
            averageRating: null,
            ratingsCount: null,
            status: 'active',
        );
    }

    private function makeResponseDto(int $id = 1, string $title = 'Clean Code'): BookResponseDto
    {
        return new BookResponseDto(
            id: $id,
            title: $title,
            slug: 'clean-code',
            authorId: 1,
            description: 'A book about writing clean code.',
            price: 29.99,
            stock: 10,
            publishYear: 2008,
            coverImage: null,
            averageRating: null,
            ratingsCount: null,
            status: 'active',
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }

    public function test_execute_calls_repository_create_with_dto(): void
    {
        $dto = $this->makeDto();
        $responseDto = $this->makeResponseDto();

        $this->repository
            ->expects($this->once())
            ->method('create')
            ->with($dto)
            ->willReturn($responseDto);

        $result = $this->service->execute($dto);

        $this->assertSame($responseDto, $result);
    }

    public function test_execute_returns_book_response_dto(): void
    {
        $dto = $this->makeDto('Refactoring');
        $responseDto = $this->makeResponseDto(2, 'Refactoring');

        $this->repository
            ->method('create')
            ->willReturn($responseDto);

        $result = $this->service->execute($dto);

        $this->assertInstanceOf(BookResponseDto::class, $result);
        $this->assertSame(2, $result->id);
        $this->assertSame('Refactoring', $result->title);
    }
}
