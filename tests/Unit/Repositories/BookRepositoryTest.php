<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Dto\Book\BookDto;
use App\Dto\Book\BookFiltersDto;
use App\Dto\Book\BookResponseDto;
use App\Models\Author;
use App\Models\Book;
use App\Repositories\Eloquent\BookRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class BookRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private BookRepository $repository;

    private Author $author;

    // -----------------------------------------------------------------------
    // getList
    // -----------------------------------------------------------------------

    public function testGetListReturnsArrayOfBookResponseDtos(): void
    {
        Book::factory()->count(3)->create(['author_id' => $this->author->id]);

        $filters = new BookFiltersDto();
        $result = $this->repository->getList($filters);

        self::assertIsArray($result);
        self::assertCount(3, $result);
        self::assertContainsOnlyInstancesOf(BookResponseDto::class, $result);
    }

    public function testGetListReturnsEmptyArrayWhenNoBooks(): void
    {
        $filters = new BookFiltersDto();
        $result = $this->repository->getList($filters);

        self::assertSame([], $result);
    }

    public function testGetListFiltersBySearch(): void
    {
        Book::factory()->create(['title' => 'Clean Code', 'author_id' => $this->author->id]);
        Book::factory()->create(['title' => 'The Pragmatic Programmer', 'author_id' => $this->author->id]);

        $filters = new BookFiltersDto(search: 'Clean');
        $result = $this->repository->getList($filters);

        self::assertCount(1, $result);
        self::assertSame('Clean Code', $result[0]->title);
    }

    public function testGetListRespectsPerPage(): void
    {
        Book::factory()->count(10)->create(['author_id' => $this->author->id]);

        $filters = new BookFiltersDto(perPage: 3);
        $result = $this->repository->getList($filters);

        self::assertCount(3, $result);
    }

    public function testGetListSortsByTitleAsc(): void
    {
        Book::factory()->create(['title' => 'ZZZ Book', 'author_id' => $this->author->id]);
        Book::factory()->create(['title' => 'AAA Book', 'author_id' => $this->author->id]);

        $filters = new BookFiltersDto(sortBy: 'title', sortDirection: 'asc');
        $result = $this->repository->getList($filters);

        self::assertSame('AAA Book', $result[0]->title);
        self::assertSame('ZZZ Book', $result[1]->title);
    }

    public function testGetListSortsByTitleDesc(): void
    {
        Book::factory()->create(['title' => 'AAA Book', 'author_id' => $this->author->id]);
        Book::factory()->create(['title' => 'ZZZ Book', 'author_id' => $this->author->id]);

        $filters = new BookFiltersDto(sortBy: 'title', sortDirection: 'desc');
        $result = $this->repository->getList($filters);

        self::assertSame('ZZZ Book', $result[0]->title);
        self::assertSame('AAA Book', $result[1]->title);
    }

    // -----------------------------------------------------------------------
    // getById
    // -----------------------------------------------------------------------

    public function testGetByIdReturnsBookResponseDto(): void
    {
        $book = Book::factory()->create(['title' => 'Refactoring', 'author_id' => $this->author->id]);

        $result = $this->repository->getById($book->id);

        self::assertInstanceOf(BookResponseDto::class, $result);
        self::assertSame($book->id, $result->id);
        self::assertSame('Refactoring', $result->title);
    }

    public function testGetByIdReturnsNullWhenNotFound(): void
    {
        $result = $this->repository->getById(99999);

        self::assertNull($result);
    }

    // -----------------------------------------------------------------------
    // create
    // -----------------------------------------------------------------------

    public function testCreatePersistsBookAndReturnsDto(): void
    {
        $dto = $this->makeDto(['title' => 'Domain-Driven Design', 'slug' => 'domain-driven-design']);

        $result = $this->repository->create($dto);

        self::assertInstanceOf(BookResponseDto::class, $result);
        self::assertSame('Domain-Driven Design', $result->title);
        $this->assertDatabaseHas('books', ['title' => 'Domain-Driven Design']);
    }

    public function testCreateAssignsIdToReturnedDto(): void
    {
        $dto = $this->makeDto();

        $result = $this->repository->create($dto);

        self::assertIsInt($result->id);
        self::assertGreaterThan(0, $result->id);
    }

    public function testCreateStoresAllFieldsCorrectly(): void
    {
        $dto = $this->makeDto([
            'title' => 'SICP',
            'slug' => 'sicp',
            'price' => 59.99,
            'stock' => 5,
            'publishYear' => 1996,
            'status' => 'active',
        ]);

        $result = $this->repository->create($dto);

        self::assertSame('SICP', $result->title);
        self::assertSame(59.99, $result->price);
        self::assertSame(5, $result->stock);
        self::assertSame(1996, $result->publishYear);
        self::assertSame('active', $result->status);
    }

    // -----------------------------------------------------------------------
    // update
    // -----------------------------------------------------------------------

    public function testUpdateChangesBookFieldsAndReturnsDto(): void
    {
        $book = Book::factory()->create(['title' => 'Old Title', 'author_id' => $this->author->id]);
        $dto = $this->makeDto(['title' => 'New Title', 'slug' => 'new-title']);

        $result = $this->repository->update($book->id, $dto); // передаём int, не модель

        self::assertInstanceOf(BookResponseDto::class, $result);
        self::assertSame('New Title', $result->title);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'New Title']);
    }

    public function testUpdateDoesNotCreateNewRecord(): void
    {
        $book = Book::factory()->create(['author_id' => $this->author->id]);
        $dto = $this->makeDto(['title' => 'Updated', 'slug' => 'updated']);

        $this->repository->update($book->id, $dto); // передаём int, не модель

        $this->assertDatabaseCount('books', 1);
    }

    // -----------------------------------------------------------------------
    // delete
    // -----------------------------------------------------------------------

    public function testDeleteRemovesBookFromDatabase(): void
    {
        $book = Book::factory()->create(['author_id' => $this->author->id]);

        $result = $this->repository->delete($book->id); // передаём int, не модель

        self::assertTrue($result);
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function testDeleteReturnsTrueOnSuccess(): void
    {
        $book = Book::factory()->create(['author_id' => $this->author->id]);

        $result = $this->repository->delete($book->id); // передаём int, не модель

        self::assertTrue($result);
    }

    public function testDeleteReturnsFalseForNonexistentBook(): void
    {
        $result = $this->repository->delete(99999);

        self::assertFalse($result);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new BookRepository();
        $this->author = Author::factory()->create();
    }

    /** @param array<string, mixed> $overrides */
    private function makeDto(array $overrides = []): BookDto
    {
        return new BookDto(
            title: (string) ($overrides['title'] ?? 'Clean Code'),
            slug: (string) ($overrides['slug'] ?? 'clean-code'),
            authorId: (int) ($overrides['authorId'] ?? $this->author->id),
            description: (string) ($overrides['description'] ?? 'A book about writing clean code.'),
            price: (float) ($overrides['price'] ?? 29.99),
            stock: (int) ($overrides['stock'] ?? 10),
            publishYear: (int) ($overrides['publishYear'] ?? 2008),
            coverImage: isset($overrides['coverImage']) ? (string) $overrides['coverImage'] : null,
            averageRating: (float) ($overrides['averageRating'] ?? 0.0),
            ratingsCount: (int) ($overrides['ratingsCount'] ?? 0),
            status: (string) ($overrides['status'] ?? 'active'),
        );
    }
}
