<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Dto\Genre\GenreDto;
use App\Dto\Genre\GenreFiltersDto;
use App\Dto\Genre\GenreResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Models\Book;
use App\Models\Genre;
use App\Repositories\Eloquent\GenreRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;

class GenreRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private GenreRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new GenreRepository();
    }

    public function test_get_list_returns_array_of_genre_response_dto(): void
    {
        Genre::factory()->count(3)->create();

        $filters = new GenreFiltersDto();
        $result = $this->repository->getList($filters);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertContainsOnlyInstancesOf(GenreResponseDto::class, $result);
    }

    public function test_get_list_filters_by_search(): void
    {
        Genre::factory()->create(['name' => 'Fantasy', 'slug' => 'fantasy']);
        Genre::factory()->create(['name' => 'Horror', 'slug' => 'horror']);

        $filters = new GenreFiltersDto(search: 'Fan');
        $result = $this->repository->getList($filters);

        $this->assertCount(1, $result);
        $this->assertSame('Fantasy', $result[0]->name);
    }

    public function test_get_list_respects_sort_direction(): void
    {
        Genre::factory()->create(['name' => 'Zzz Genre', 'slug' => 'zzz-genre']);
        Genre::factory()->create(['name' => 'Aaa Genre', 'slug' => 'aaa-genre']);

        $filters = new GenreFiltersDto(sortBy: 'name', sortDirection: 'asc');
        $result = $this->repository->getList($filters);

        $this->assertSame('Aaa Genre', $result[0]->name);
        $this->assertSame('Zzz Genre', $result[1]->name);
    }

    public function test_get_list_returns_empty_array_when_no_genres(): void
    {
        $filters = new GenreFiltersDto();
        $result = $this->repository->getList($filters);

        $this->assertSame([], $result);
    }

    public function test_get_web_list_by_ids_returns_paginated_response_dto(): void
    {
        $genres = Genre::factory()->count(3)->create();
        $ids = $genres->pluck('id')->all();

        $filters = new GenreFiltersDto();
        $result = $this->repository->getWebListByIds($ids, $filters);

        $this->assertInstanceOf(PaginatedResponseDto::class, $result);
        $this->assertSame(3, $result->total);
    }

    public function test_get_web_list_by_ids_returns_only_matching_ids(): void
    {
        $genres = Genre::factory()->count(3)->create();
        $ids = $genres->pluck('id')->take(2)->all();

        $filters = new GenreFiltersDto();
        $result = $this->repository->getWebListByIds($ids, $filters);

        $this->assertSame(2, $result->total);
    }

    public function test_get_web_list_by_ids_returns_correct_pagination_metadata(): void
    {
        $genres = Genre::factory()->count(5)->create();
        $ids = $genres->pluck('id')->all();

        $filters = new GenreFiltersDto(perPage: 2);
        $result = $this->repository->getWebListByIds($ids, $filters);

        $this->assertSame(5, $result->total);
        $this->assertSame(2, $result->perPage);
        $this->assertSame(3, $result->lastPage);
        $this->assertSame(1, $result->currentPage);
    }

    public function test_get_all_returns_all_genres_ordered_by_name(): void
    {
        Genre::factory()->create(['name' => 'Zzz', 'slug' => 'zzz']);
        Genre::factory()->create(['name' => 'Aaa', 'slug' => 'aaa']);
        Genre::factory()->create(['name' => 'Mmm', 'slug' => 'mmm']);

        $result = $this->repository->getAll();

        $this->assertCount(3, $result);
        $this->assertSame('Aaa', $result[0]->name);
        $this->assertSame('Mmm', $result[1]->name);
        $this->assertSame('Zzz', $result[2]->name);
    }

    public function test_get_all_returns_array_of_genre_response_dto(): void
    {
        Genre::factory()->count(2)->create();

        $result = $this->repository->getAll();

        $this->assertContainsOnlyInstancesOf(GenreResponseDto::class, $result);
    }

    public function test_get_all_returns_empty_array_when_no_genres(): void
    {
        $result = $this->repository->getAll();

        $this->assertSame([], $result);
    }

    public function test_get_by_id_returns_genre_response_dto(): void
    {
        $genre = Genre::factory()->create();

        $result = $this->repository->getById($genre->id);

        $this->assertInstanceOf(GenreResponseDto::class, $result);
        $this->assertSame($genre->id, $result->id);
        $this->assertSame($genre->name, $result->name);
        $this->assertSame($genre->slug, $result->slug);
    }

    public function test_get_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->getById(999);

        $this->assertNull($result);
    }

    public function test_find_by_id_with_relations_returns_genre_response_dto(): void
    {
        $genre = Genre::factory()->create();

        $result = $this->repository->findByIdWithRelations($genre->id);

        $this->assertInstanceOf(GenreResponseDto::class, $result);
        $this->assertSame($genre->id, $result->id);
    }

    public function test_find_by_id_with_relations_includes_books_count(): void
    {
        $genre = Genre::factory()->create();

        $result = $this->repository->findByIdWithRelations($genre->id);

        $this->assertSame(0, $result->booksCount);
    }

    public function test_find_by_id_with_relations_throws_when_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->findByIdWithRelations(999);
    }

    public function test_suggest_returns_matching_genres(): void
    {
        Genre::factory()->create(['name' => 'Fantasy', 'slug' => 'fantasy']);
        Genre::factory()->create(['name' => 'Horror', 'slug' => 'horror']);
        Genre::factory()->create(['name' => 'Science Fiction', 'slug' => 'science-fiction']);

        $result = $this->repository->suggest('fan');

        $this->assertCount(1, $result);
        $this->assertSame('Fantasy', $result[0]->name);
    }

    public function test_suggest_returns_array_of_genre_response_dto(): void
    {
        Genre::factory()->create(['name' => 'Fantasy', 'slug' => 'fantasy']);

        $result = $this->repository->suggest('fan');

        $this->assertContainsOnlyInstancesOf(GenreResponseDto::class, $result);
    }

    public function test_suggest_returns_empty_array_when_no_match(): void
    {
        Genre::factory()->create(['name' => 'Fantasy', 'slug' => 'fantasy']);

        $result = $this->repository->suggest('xyz');

        $this->assertSame([], $result);
    }

    public function test_suggest_limits_to_20_results(): void
    {
        foreach (range(1, 15) as $i) {
            Genre::factory()->create(['name' => "Test Genre {$i}", 'slug' => "test-genre-{$i}"]);
        }

        $result = $this->repository->suggest('Test');

        $this->assertLessThanOrEqual(20, count($result));
    }

    public function test_suggest_returns_results_ordered_by_name(): void
    {
        Genre::factory()->create(['name' => 'Thriller', 'slug' => 'thriller']);
        Genre::factory()->create(['name' => 'Teen Fiction', 'slug' => 'teen-fiction']);

        $result = $this->repository->suggest('T');

        $this->assertSame('Teen Fiction', $result[0]->name);
        $this->assertSame('Thriller', $result[1]->name);
    }

    public function test_create_persists_genre_to_database(): void
    {
        $dto = new GenreDto(name: 'Fantasy', slug: 'fantasy');

        $this->repository->create($dto);

        $this->assertDatabaseHas('genres', ['name' => 'Fantasy', 'slug' => 'fantasy']);
    }

    public function test_create_returns_genre_response_dto(): void
    {
        $dto = new GenreDto(name: 'Fantasy', slug: 'fantasy');

        $result = $this->repository->create($dto);

        $this->assertInstanceOf(GenreResponseDto::class, $result);
        $this->assertSame('Fantasy', $result->name);
        $this->assertSame('fantasy', $result->slug);
    }

    public function test_create_assigns_an_id(): void
    {
        $dto = new GenreDto(name: 'Fantasy', slug: 'fantasy');

        $result = $this->repository->create($dto);

        $this->assertGreaterThan(0, $result->id);
    }

    public function test_update_modifies_genre_in_database(): void
    {
        $genre = Genre::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);
        $dto = new GenreDto(name: 'New Name', slug: 'new-name');

        $this->repository->update($genre->id, $dto);

        $this->assertDatabaseHas('genres', ['id' => $genre->id, 'name' => 'New Name', 'slug' => 'new-name']);
    }

    public function test_update_returns_updated_genre_response_dto(): void
    {
        $genre = Genre::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);
        $dto = new GenreDto(name: 'New Name', slug: 'new-name');

        $result = $this->repository->update($genre->id, $dto);

        $this->assertInstanceOf(GenreResponseDto::class, $result);
        $this->assertSame('New Name', $result->name);
        $this->assertSame('new-name', $result->slug);
    }

    public function test_update_throws_when_genre_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $dto = new GenreDto(name: 'New Name', slug: 'new-name');
        $this->repository->update(999, $dto);
    }

    public function test_delete_removes_genre_from_database(): void
    {
        $genre = Genre::factory()->create();

        $this->repository->delete($genre->id);

        $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
    }

    public function test_delete_returns_true_on_success(): void
    {
        $genre = Genre::factory()->create();

        $result = $this->repository->delete($genre->id);

        $this->assertTrue($result);
    }

    public function test_delete_throws_when_genre_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->delete(999);
    }
}
