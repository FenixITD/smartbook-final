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

class BookRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private BookRepository $repository;

    private Author $author;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new BookRepository;
        $this->author = Author::factory()->create();
    }

    private function makeDto(array $overrides = []): BookDto
    {
        return new BookDto(
            title: $overrides['title'] ?? 'Clean Code',
            slug: $overrides['slug'] ?? 'clean-code',
            authorId: $overrides['authorId'] ?? $this->author->id,
            description: $overrides['description'] ?? 'A book about writing clean code.',
            price: $overrides['price'] ?? 29.99,
            stock: $overrides['stock'] ?? 10,
            publishYear: $overrides['publishYear'] ?? 2008,
            coverImage: $overrides['coverImage'] ?? null,
            averageRating: $overrides['averageRating'] ?? 0.0,
            ratingsCount: $overrides['ratingsCount'] ?? 0,
            status: $overrides['status'] ?? 'published',
        );
    }

    // -----------------------------------------------------------------------
    // getList
    // -----------------------------------------------------------------------

    public function test_get_list_returns_array_of_book_response_dtos(): void
    {
        Book::factory()->count(3)->create(['author_id' => $this->author->id]);

        $filters = new BookFiltersDto;
        $result = $this->repository->getList($filters);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertContainsOnlyInstancesOf(BookResponseDto::class, $result);
    }

    public function test_get_list_returns_empty_array_when_no_books(): void
    {
        $filters = new BookFiltersDto;
        $result = $this->repository->getList($filters);

        $this->assertSame([], $result);
    }

    public function test_get_list_filters_by_search(): void
    {
        Book::factory()->create(['title' => 'Clean Code', 'author_id' => $this->author->id]);
        Book::factory()->create(['title' => 'The Pragmatic Programmer', 'author_id' => $this->author->id]);

        $filters = new BookFiltersDto(search: 'Clean');
        $result = $this->repository->getList($filters);

        $this->assertCount(1, $result);
        $this->assertSame('Clean Code', $result[0]->title);
    }

    public function test_get_list_respects_per_page(): void
    {
        Book::factory()->count(10)->create(['author_id' => $this->author->id]);

        $filters = new BookFiltersDto(perPage: 3);
        $result = $this->repository->getList($filters);

        $this->assertCount(3, $result);
    }

    public function test_get_list_sorts_by_title_asc(): void
    {
        Book::factory()->create(['title' => 'ZZZ Book', 'author_id' => $this->author->id]);
        Book::factory()->create(['title' => 'AAA Book', 'author_id' => $this->author->id]);

        $filters = new BookFiltersDto(sortBy: 'title', sortDirection: 'asc');
        $result = $this->repository->getList($filters);

        $this->assertSame('AAA Book', $result[0]->title);
        $this->assertSame('ZZZ Book', $result[1]->title);
    }

    public function test_get_list_sorts_by_title_desc(): void
    {
        Book::factory()->create(['title' => 'AAA Book', 'author_id' => $this->author->id]);
        Book::factory()->create(['title' => 'ZZZ Book', 'author_id' => $this->author->id]);

        $filters = new BookFiltersDto(sortBy: 'title', sortDirection: 'desc');
        $result = $this->repository->getList($filters);

        $this->assertSame('ZZZ Book', $result[0]->title);
        $this->assertSame('AAA Book', $result[1]->title);
    }

    // -----------------------------------------------------------------------
    // getById
    // -----------------------------------------------------------------------

    public function test_get_by_id_returns_book_response_dto(): void
    {
        $book = Book::factory()->create(['title' => 'Refactoring', 'author_id' => $this->author->id]);

        $result = $this->repository->getById($book->id);

        $this->assertInstanceOf(BookResponseDto::class, $result);
        $this->assertSame($book->id, $result->id);
        $this->assertSame('Refactoring', $result->title);
    }

    public function test_get_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->getById(99999);

        $this->assertNull($result);
    }

    // -----------------------------------------------------------------------
    // create
    // -----------------------------------------------------------------------

    public function test_create_persists_book_and_returns_dto(): void
    {
        $dto = $this->makeDto(['title' => 'Domain-Driven Design', 'slug' => 'domain-driven-design']);

        $result = $this->repository->create($dto);

        $this->assertInstanceOf(BookResponseDto::class, $result);
        $this->assertSame('Domain-Driven Design', $result->title);
        $this->assertDatabaseHas('books', ['title' => 'Domain-Driven Design']);
    }

    public function test_create_assigns_id_to_returned_dto(): void
    {
        $dto = $this->makeDto();

        $result = $this->repository->create($dto);

        $this->assertIsInt($result->id);
        $this->assertGreaterThan(0, $result->id);
    }

    public function test_create_stores_all_fields_correctly(): void
    {
        $dto = $this->makeDto([
            'title' => 'SICP',
            'slug' => 'sicp',
            'price' => 59.99,
            'stock' => 5,
            'publishYear' => 1996,
            'status' => 'published',
        ]);

        $result = $this->repository->create($dto);

        $this->assertSame('SICP', $result->title);
        $this->assertSame(59.99, $result->price);
        $this->assertSame(5, $result->stock);
        $this->assertSame(1996, $result->publishYear);
        $this->assertSame('published', $result->status);
    }

    // -----------------------------------------------------------------------
    // update
    // -----------------------------------------------------------------------

    public function test_update_changes_book_fields_and_returns_dto(): void
    {
        $book = Book::factory()->create(['title' => 'Old Title', 'author_id' => $this->author->id]);
        $dto = $this->makeDto(['title' => 'New Title', 'slug' => 'new-title']);

        $result = $this->repository->update($book, $dto);

        $this->assertInstanceOf(BookResponseDto::class, $result);
        $this->assertSame('New Title', $result->title);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'New Title']);
    }

    public function test_update_does_not_create_new_record(): void
    {
        $book = Book::factory()->create(['author_id' => $this->author->id]);
        $dto = $this->makeDto(['title' => 'Updated', 'slug' => 'updated']);

        $this->repository->update($book, $dto);

        $this->assertDatabaseCount('books', 1);
    }

    // -----------------------------------------------------------------------
    // delete
    // -----------------------------------------------------------------------

    public function test_delete_removes_book_from_database(): void
    {
        $book = Book::factory()->create(['author_id' => $this->author->id]);

        $result = $this->repository->delete($book);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_delete_returns_true_on_success(): void
    {
        $book = Book::factory()->create(['author_id' => $this->author->id]);

        $result = $this->repository->delete($book);

        $this->assertTrue($result);
    }
}
