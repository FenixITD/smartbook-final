<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Book;

use App\Dto\Book\BookDto;
use App\Dto\Book\BookResponseDto;
use App\Models\Book;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Services\Book\UpdateBookService;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class UpdateBookServiceTest extends TestCase
{
    private BookRepositoryInterface&MockObject $repository;
    private UpdateBookService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(BookRepositoryInterface::class);
        $this->service = new UpdateBookService($this->repository);
    }

    private function makeDto(string $title = 'New Title'): BookDto
    {
        return new BookDto(
            title: $title,
            slug: 'new-title',
            authorId: 1,
            description: 'Updated description.',
            price: 39.99,
            stock: 5,
            publishYear: 2020,
            coverImage: null,
            averageRating: null,
            ratingsCount: null,
            status: 'active',
        );
    }

    private function makeResponseDto(int $id = 1, string $title = 'New Title'): BookResponseDto
    {
        return new BookResponseDto(
            id: $id,
            title: $title,
            slug: 'new-title',
            authorId: 1,
            description: 'Updated description.',
            price: 39.99,
            stock: 5,
            publishYear: 2020,
            coverImage: null,
            averageRating: null,
            ratingsCount: null,
            status: 'active',
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-06-01 00:00:00',
        );
    }

    public function test_execute_calls_repository_update_with_correct_arguments(): void
    {
        $book = new Book(['title' => 'Old Title']);
        $book->id = 1;

        $dto = $this->makeDto('New Title');
        $responseDto = $this->makeResponseDto(1, 'New Title');

        $this->repository
            ->expects($this->once())
            ->method('update')
            ->with($book, $dto)
            ->willReturn($responseDto);

        $result = $this->service->execute($book, $dto);

        $this->assertSame($responseDto, $result);
    }

    public function test_execute_returns_updated_book_response_dto(): void
    {
        $book = new Book(['title' => 'Design Patterns']);
        $book->id = 3;

        $dto = $this->makeDto('Design Patterns Updated');
        $responseDto = $this->makeResponseDto(3, 'Design Patterns Updated');

        $this->repository
            ->method('update')
            ->willReturn($responseDto);

        $result = $this->service->execute($book, $dto);

        $this->assertInstanceOf(BookResponseDto::class, $result);
        $this->assertSame('Design Patterns Updated', $result->title);
        $this->assertSame(3, $result->id);
    }
}
