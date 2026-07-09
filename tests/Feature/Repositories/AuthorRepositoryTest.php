<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Dto\Author\AuthorDto;
use App\Dto\Author\AuthorFiltersDto;
use App\Dto\Author\AuthorResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Models\Author;
use App\Repositories\Eloquent\AuthorRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AuthorRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AuthorRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new AuthorRepository();
    }

    public function test_get_list_returns_array_of_author_response_dtos(): void
    {
        Author::factory()->count(3)->create();

        $result = $this->repository->getList(new AuthorFiltersDto());

        $this->assertIsArray($result);
        $this->assertContainsOnlyInstancesOf(AuthorResponseDto::class, $result);
    }

    public function test_get_list_returns_all_authors_when_no_search(): void
    {
        Author::factory()->count(3)->create();

        $result = $this->repository->getList(new AuthorFiltersDto());

        $this->assertCount(3, $result);
    }

    public function test_get_list_filters_by_search(): void
    {
        Author::factory()->create(['name' => 'George Orwell']);
        Author::factory()->create(['name' => 'John Steinbeck']);

        $result = $this->repository->getList(new AuthorFiltersDto(search: 'George'));

        $this->assertCount(1, $result);
        $this->assertSame('George Orwell', $result[0]->name);
    }

    public function test_get_list_search_is_case_insensitive(): void
    {
        Author::factory()->create(['name' => 'George Orwell']);
        Author::factory()->create(['name' => 'John Steinbeck']);

        $result = $this->repository->getList(new AuthorFiltersDto(search: 'george'));

        $this->assertCount(1, $result);
    }

    public function test_get_list_returns_empty_array_when_search_has_no_matches(): void
    {
        Author::factory()->create(['name' => 'George Orwell']);

        $result = $this->repository->getList(new AuthorFiltersDto(search: 'Tolkien'));

        $this->assertCount(0, $result);
    }

    public function test_get_list_sorts_by_name_ascending(): void
    {
        Author::factory()->create(['name' => 'Zola']);
        Author::factory()->create(['name' => 'Austen']);
        Author::factory()->create(['name' => 'Kafka']);

        $result = $this->repository->getList(new AuthorFiltersDto(sortBy: 'name', sortDirection: 'asc'));

        $this->assertSame('Austen', $result[0]->name);
        $this->assertSame('Kafka', $result[1]->name);
        $this->assertSame('Zola', $result[2]->name);
    }

    public function test_get_list_sorts_by_name_descending(): void
    {
        Author::factory()->create(['name' => 'Zola']);
        Author::factory()->create(['name' => 'Austen']);

        $result = $this->repository->getList(new AuthorFiltersDto(sortBy: 'name', sortDirection: 'desc'));

        $this->assertSame('Zola', $result[0]->name);
        $this->assertSame('Austen', $result[1]->name);
    }

    public function test_get_web_list_by_ids_returns_paginated_response_dto(): void
    {
        $authors = Author::factory()->count(3)->create();
        $ids = $authors->pluck('id')->all();

        $result = $this->repository->getWebListByIds($ids, count($ids), new AuthorFiltersDto());

        $this->assertInstanceOf(PaginatedResponseDto::class, $result);
    }

    public function test_get_web_list_by_ids_returns_only_matching_ids(): void
    {
        $included = Author::factory()->count(2)->create();
        Author::factory()->count(2)->create();

        $ids = $included->pluck('id')->all();

        $result = $this->repository->getWebListByIds($ids, count($ids), new AuthorFiltersDto());

        $this->assertCount(2, $result->items);
    }

    public function test_get_web_list_by_ids_returns_empty_when_ids_are_empty(): void
    {
        Author::factory()->count(3)->create();

        $result = $this->repository->getWebListByIds([], is_countable([]) ? count([]) : (is_array([]) ? count([]) : 0), new AuthorFiltersDto());

        $this->assertCount(0, $result->items);
    }

    public function test_get_all_returns_array_of_author_response_dtos(): void
    {
        Author::factory()->count(3)->create();

        $result = $this->repository->getAll();

        $this->assertIsArray($result);
        $this->assertContainsOnlyInstancesOf(AuthorResponseDto::class, $result);
    }

    public function test_get_all_returns_authors_sorted_by_name(): void
    {
        Author::factory()->create(['name' => 'Zola']);
        Author::factory()->create(['name' => 'Austen']);

        $result = $this->repository->getAll();

        $this->assertSame('Austen', $result[0]->name);
        $this->assertSame('Zola', $result[1]->name);
    }

    public function test_get_all_limits_to_200(): void
    {
        Author::factory()->count(205)->create();

        $result = $this->repository->getAll();

        $this->assertCount(200, $result);
    }

    public function test_get_by_id_returns_author_response_dto(): void
    {
        $author = Author::factory()->create(['name' => 'Leo Tolstoy']);

        $result = $this->repository->getById($author->id);

        $this->assertInstanceOf(AuthorResponseDto::class, $result);
        $this->assertSame($author->id, $result->id);
        $this->assertSame('Leo Tolstoy', $result->name);
    }

    public function test_get_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->getById(99999);

        $this->assertNull($result);
    }

    public function test_find_by_id_with_relations_returns_author_response_dto(): void
    {
        $author = Author::factory()->create(['name' => 'Dostoevsky']);

        $result = $this->repository->findByIdWithRelations($author->id);

        $this->assertInstanceOf(AuthorResponseDto::class, $result);
        $this->assertSame($author->id, $result->id);
    }

    public function test_find_by_id_with_relations_throws_when_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->findByIdWithRelations(99999);
    }

    public function test_find_by_id_with_relations_includes_books_count(): void
    {
        $author = Author::factory()->create();
        $author->books()->createMany([
            ['title' => 'Book A', 'slug' => 'book-a', 'description' => 'desc', 'price' => 10, 'stock' => 1, 'status' => 'active'],
            ['title' => 'Book B', 'slug' => 'book-b', 'description' => 'desc', 'price' => 10, 'stock' => 1, 'status' => 'active'],
        ]);

        $result = $this->repository->findByIdWithRelations($author->id);

        $this->assertSame(2, $result->booksCount);
    }

    public function test_create_persists_author_to_database(): void
    {
        $this->repository->create(new AuthorDto(name: 'Albert Camus'));

        $this->assertDatabaseHas('authors', ['name' => 'Albert Camus']);
    }

    public function test_create_returns_author_response_dto(): void
    {
        $result = $this->repository->create(new AuthorDto(name: 'Albert Camus'));

        $this->assertInstanceOf(AuthorResponseDto::class, $result);
        $this->assertSame('Albert Camus', $result->name);
        $this->assertGreaterThan(0, $result->id);
    }

    public function test_update_changes_author_name_in_database(): void
    {
        $author = Author::factory()->create(['name' => 'Old Name']);

        $this->repository->update($author->id, new AuthorDto(name: 'New Name'));

        $this->assertDatabaseHas('authors', ['id' => $author->id, 'name' => 'New Name']);
    }

    public function test_update_returns_author_response_dto_with_new_name(): void
    {
        $author = Author::factory()->create(['name' => 'Old Name']);

        $result = $this->repository->update($author->id, new AuthorDto(name: 'New Name'));

        $this->assertInstanceOf(AuthorResponseDto::class, $result);
        $this->assertSame('New Name', $result->name);
    }

    public function test_update_throws_when_author_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->update(99999, new AuthorDto(name: 'Name'));
    }

    public function test_delete_removes_author_from_database(): void
    {
        $author = Author::factory()->create();

        $this->repository->delete($author->id);

        $this->assertDatabaseMissing('authors', ['id' => $author->id]);
    }

    public function test_delete_returns_true_on_success(): void
    {
        $author = Author::factory()->create();

        $result = $this->repository->delete($author->id);

        $this->assertTrue($result);
    }

    public function test_delete_throws_when_author_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->delete(99999);
    }
}
