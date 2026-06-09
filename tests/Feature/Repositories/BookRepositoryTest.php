<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Dto\Book\BookDto;
use App\Dto\Book\BookFiltersDto;
use App\Dto\Book\BookResponseDto;
use App\Dto\Dashboard\DashboardFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Repositories\Eloquent\BookRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private BookRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new BookRepository();
    }

    private function makeBookDto(array $overrides = []): BookDto
    {
        $author = Author::factory()->create();

        return new BookDto(
            title: $overrides['title'] ?? 'Test Book',
            slug: $overrides['slug'] ?? 'test-book',
            authorId: $overrides['authorId'] ?? $author->id,
            description: $overrides['description'] ?? 'Some description',
            price: $overrides['price'] ?? 19.99,
            stock: $overrides['stock'] ?? 10,
            publishYear: $overrides['publishYear'] ?? 2024,
            coverImage: $overrides['coverImage'] ?? null,
            status: $overrides['status'] ?? 'active',
        );
    }

    public function test_get_list_returns_array_of_book_response_dtos(): void
    {
        Book::factory()->for(Author::factory())->count(3)->create();

        $filters = new BookFiltersDto();
        $result = $this->repository->getList($filters);

        $this->assertCount(3, $result);
        $this->assertContainsOnlyInstancesOf(BookResponseDto::class, $result);
    }

    public function test_get_list_respects_sort_by_and_direction(): void
    {
        $author = Author::factory()->create();
        Book::factory()->create(['title' => 'Zebra', 'author_id' => $author->id]);
        Book::factory()->create(['title' => 'Apple', 'author_id' => $author->id]);

        $filters = new BookFiltersDto(sortBy: 'title', sortDirection: 'asc');
        $result = $this->repository->getList($filters);

        $this->assertSame('Apple', $result[0]->title);
        $this->assertSame('Zebra', $result[1]->title);
    }

    public function test_get_list_respects_per_page(): void
    {
        Book::factory()->for(Author::factory())->count(5)->create();

        $filters = new BookFiltersDto(perPage: 2);
        $result = $this->repository->getList($filters);

        $this->assertCount(2, $result);
    }

    public function test_get_list_by_ids_returns_only_requested_books(): void
    {
        $books = Book::factory()->for(Author::factory())->count(5)->create();
        $ids = $books->take(2)->pluck('id')->all();

        $filters = new BookFiltersDto();
        $result = $this->repository->getListByIds($ids, $filters);

        $this->assertCount(2, $result);
        $resultIds = array_map(fn (BookResponseDto $dto) => $dto->id, $result);
        foreach ($ids as $id) {
            $this->assertContains($id, $resultIds);
        }
    }

    public function test_get_list_by_ids_preserves_order(): void
    {
        $books = Book::factory()->for(Author::factory())->count(3)->create();
        $ids = $books->pluck('id')->reverse()->values()->all();

        $filters = new BookFiltersDto();
        $result = $this->repository->getListByIds($ids, $filters);

        $this->assertSame($ids[0], $result[0]->id);
        $this->assertSame($ids[1], $result[1]->id);
        $this->assertSame($ids[2], $result[2]->id);
    }

    public function test_get_web_list_returns_paginated_response_dto(): void
    {
        Book::factory()->for(Author::factory())->count(3)->create();

        $filters = new BookFiltersDto();
        $result = $this->repository->getWebList($filters);

        $this->assertInstanceOf(PaginatedResponseDto::class, $result);
        $this->assertSame(3, $result->total);
    }

    public function test_get_web_list_loads_author_and_genres_relations(): void
    {
        $book = Book::factory()->for(Author::factory())->create();
        $genre = Genre::factory()->create();
        $book->genres()->attach($genre->id);

        $filters = new BookFiltersDto();
        $result = $this->repository->getWebList($filters);

        $item = $result->items[0];
        $this->assertTrue($item->relationLoaded('author'));
        $this->assertTrue($item->relationLoaded('genres'));
    }

    public function test_get_web_list_by_ids_returns_only_requested_books(): void
    {
        $books = Book::factory()->for(Author::factory())->count(5)->create();
        $ids = $books->take(3)->pluck('id')->all();

        $filters = new BookFiltersDto();
        $result = $this->repository->getWebListByIds($ids, $filters);

        $this->assertSame(3, $result->total);
    }

    public function test_get_by_ids_with_author_loads_author_relation(): void
    {
        $books = Book::factory()->for(Author::factory())->count(2)->create();
        $ids = $books->pluck('id')->all();

        $result = $this->repository->getByIdsWithAuthor($ids, 15);

        $this->assertInstanceOf(PaginatedResponseDto::class, $result);
        foreach ($result->items as $item) {
            $this->assertTrue($item->relationLoaded('author'));
        }
    }

    public function test_get_dashboard_list_by_ids_sorts_by_price_asc(): void
    {
        $author = Author::factory()->create();
        $cheap = Book::factory()->create(['price' => 5.00, 'author_id' => $author->id]);
        $expensive = Book::factory()->create(['price' => 50.00, 'author_id' => $author->id]);
        $ids = [$cheap->id, $expensive->id];

        $filters = new DashboardFiltersDto(sort: 'price_asc');
        $result = $this->repository->getDashboardListByIds($ids, $filters);

        $this->assertSame($cheap->id, $result->items[0]->id);
    }

    public function test_get_dashboard_list_by_ids_sorts_by_price_desc(): void
    {
        $author = Author::factory()->create();
        $cheap = Book::factory()->create(['price' => 5.00, 'author_id' => $author->id]);
        $expensive = Book::factory()->create(['price' => 50.00, 'author_id' => $author->id]);
        $ids = [$cheap->id, $expensive->id];

        $filters = new DashboardFiltersDto(sort: 'price_desc');
        $result = $this->repository->getDashboardListByIds($ids, $filters);

        $this->assertSame($expensive->id, $result->items[0]->id);
    }

    public function test_get_dashboard_list_by_ids_sorts_by_newest(): void
    {
        $author = Author::factory()->create();
        $old = Book::factory()->create(['publish_year' => 2000, 'author_id' => $author->id]);
        $new = Book::factory()->create(['publish_year' => 2024, 'author_id' => $author->id]);
        $ids = [$old->id, $new->id];

        $filters = new DashboardFiltersDto(sort: 'newest');
        $result = $this->repository->getDashboardListByIds($ids, $filters);

        $this->assertSame($new->id, $result->items[0]->id);
    }

    public function test_get_dashboard_list_by_ids_sorts_by_rating_by_default(): void
    {
        $author = Author::factory()->create();
        $lowRated = Book::factory()->create(['average_rating' => 1.00, 'author_id' => $author->id]);
        $highRated = Book::factory()->create(['average_rating' => 4.99, 'author_id' => $author->id]);
        $ids = [$lowRated->id, $highRated->id];

        $filters = new DashboardFiltersDto(sort: 'rating');
        $result = $this->repository->getDashboardListByIds($ids, $filters);

        $this->assertSame($highRated->id, $result->items[0]->id);
    }

    public function test_get_by_id_returns_book_response_dto_when_found(): void
    {
        $book = Book::factory()->for(Author::factory())->create();

        $result = $this->repository->getById($book->id);

        $this->assertInstanceOf(BookResponseDto::class, $result);
        $this->assertSame($book->id, $result->id);
    }

    public function test_get_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->getById(99999);

        $this->assertNull($result);
    }

    public function test_get_total_by_ids_and_quantities_calculates_correctly(): void
    {
        $author = Author::factory()->create();
        $book1 = Book::factory()->create(['price' => 10.00, 'author_id' => $author->id]);
        $book2 = Book::factory()->create(['price' => 20.00, 'author_id' => $author->id]);

        $result = $this->repository->getTotalByIdsAndQuantities([
            $book1->id => 2,
            $book2->id => 3,
        ]);

        $this->assertEqualsWithDelta(80.00, $result, 0.001);
    }

    public function test_find_by_id_with_relations_loads_author_and_genres(): void
    {
        $book = Book::factory()->for(Author::factory())->create();
        $genre = Genre::factory()->create();
        $book->genres()->attach($genre->id);

        $result = $this->repository->findByIdWithRelations($book->id);

        $this->assertInstanceOf(BookResponseDto::class, $result);
        $this->assertNotEmpty($result->genres);
        $this->assertSame($genre->id, $result->genres[0]->id);
    }

    public function test_find_by_id_with_relations_throws_when_not_found(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->repository->findByIdWithRelations(99999);
    }

    public function test_find_by_ids_with_author_returns_array_of_dtos_with_author_name(): void
    {
        $books = Book::factory()->for(Author::factory())->count(2)->create();
        $ids = $books->pluck('id')->all();

        $result = $this->repository->findByIdsWithAuthor($ids);

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(BookResponseDto::class, $result);
        foreach ($result as $dto) {
            $this->assertNotNull($dto->authorName);
        }
    }

    public function test_sync_book_genres_attaches_genres(): void
    {
        $book = Book::factory()->for(Author::factory())->create();
        $genres = Genre::factory()->count(3)->create();
        $genreIds = $genres->pluck('id')->all();

        $this->repository->syncBookGenres($book->id, $genreIds);

        $this->assertCount(3, $book->fresh()->genres);
    }

    public function test_sync_book_genres_detaches_removed_genres(): void
    {
        $book = Book::factory()->for(Author::factory())->create();
        $genres = Genre::factory()->count(3)->create();
        $book->genres()->attach($genres->pluck('id'));

        $keepGenreId = [$genres->first()->id];
        $this->repository->syncBookGenres($book->id, $keepGenreId);

        $this->assertCount(1, $book->fresh()->genres);
        $this->assertSame($keepGenreId[0], $book->fresh()->genres->first()->id);
    }

    public function test_get_ordered_by_ids_returns_paginated_response_in_correct_order(): void
    {
        $books = Book::factory()->for(Author::factory())->count(3)->create();
        $ids = $books->pluck('id')->reverse()->values()->all();

        $result = $this->repository->getOrderedByIds($ids, 15);

        $this->assertInstanceOf(PaginatedResponseDto::class, $result);
        $this->assertSame($ids[0], $result->items[0]->id);
        $this->assertSame($ids[1], $result->items[1]->id);
        $this->assertSame($ids[2], $result->items[2]->id);
    }

    public function test_create_persists_book_and_returns_dto(): void
    {
        $dto = $this->makeBookDto();

        $result = $this->repository->create($dto);

        $this->assertInstanceOf(BookResponseDto::class, $result);
        $this->assertDatabaseHas('books', ['slug' => 'test-book']);
        $this->assertSame('Test Book', $result->title);
        $this->assertSame('test-book', $result->slug);
    }

    public function test_update_changes_book_data_and_returns_dto(): void
    {
        $book = Book::factory()->for(Author::factory())->create();
        $dto = $this->makeBookDto(['title' => 'Updated Title', 'slug' => 'updated-slug', 'authorId' => $book->author_id]);

        $result = $this->repository->update($book->id, $dto);

        $this->assertInstanceOf(BookResponseDto::class, $result);
        $this->assertSame('Updated Title', $result->title);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'Updated Title']);
    }

    public function test_update_throws_when_book_not_found(): void
    {
        $dto = $this->makeBookDto();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->repository->update(99999, $dto);
    }

    public function test_delete_removes_book_from_database(): void
    {
        $book = Book::factory()->for(Author::factory())->create();

        $result = $this->repository->delete($book->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_delete_throws_when_book_not_found(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->repository->delete(99999);
    }

    public function test_get_web_list_pagination_metadata_is_correct(): void
    {
        Book::factory()->for(Author::factory())->count(5)->create();

        $filters = new BookFiltersDto(perPage: 2);
        $result = $this->repository->getWebList($filters);

        $this->assertSame(5, $result->total);
        $this->assertSame(2, $result->perPage);
        $this->assertSame(1, $result->currentPage);
        $this->assertSame(3, $result->lastPage);
    }

    public function test_get_list_returns_empty_array_when_no_books(): void
    {
        $filters = new BookFiltersDto();
        $result = $this->repository->getList($filters);

        $this->assertSame([], $result);
    }

    public function test_get_total_by_ids_and_quantities_returns_zero_for_empty_input(): void
    {
        $result = $this->repository->getTotalByIdsAndQuantities([]);

        $this->assertEqualsWithDelta(0.0, $result, 0.001);
    }

    public function test_create_book_has_correct_price_and_stock(): void
    {
        $dto = $this->makeBookDto(['price' => 29.99, 'stock' => 42]);

        $result = $this->repository->create($dto);

        $this->assertEqualsWithDelta(29.99, $result->price, 0.001);
        $this->assertSame(42, $result->stock);
    }

    public function test_find_by_id_with_relations_has_author_name(): void
    {
        $book = Book::factory()->for(Author::factory())->create();

        $result = $this->repository->findByIdWithRelations($book->id);

        $this->assertNotNull($result->authorName);
    }
}
