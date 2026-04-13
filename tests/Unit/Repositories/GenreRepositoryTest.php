<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Dto\Genre\GenreDto;
use App\Dto\Genre\GenreFiltersDto;
use App\Dto\Genre\GenreResponseDto;
use App\Models\Genre;
use App\Repositories\Eloquent\GenreRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private GenreRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new GenreRepository;
    }

    private function makeDto(array $overrides = []): GenreDto
    {
        return new GenreDto(
            name: $overrides['name'] ?? 'Science Fiction',
            slug: $overrides['slug'] ?? 'science-fiction',
            description: $overrides['description'] ?? 'A genre about science and the future.',
        );
    }

    // -----------------------------------------------------------------------
    // getList
    // -----------------------------------------------------------------------

    public function test_get_list_returns_array_of_genre_response_dtos(): void
    {
        Genre::factory()->count(3)->create();

        $filters = new GenreFiltersDto;
        $result = $this->repository->getList($filters);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertContainsOnlyInstancesOf(GenreResponseDto::class, $result);
    }

    public function test_get_list_returns_empty_array_when_no_genres(): void
    {
        $filters = new GenreFiltersDto;
        $result = $this->repository->getList($filters);

        $this->assertSame([], $result);
    }

    public function test_get_list_filters_by_search(): void
    {
        Genre::factory()->create(['name' => 'Science Fiction']);
        Genre::factory()->create(['name' => 'Fantasy']);

        $filters = new GenreFiltersDto(search: 'Science');
        $result = $this->repository->getList($filters);

        $this->assertCount(1, $result);
        $this->assertSame('Science Fiction', $result[0]->name);
    }

    public function test_get_list_respects_per_page(): void
    {
        Genre::factory()->count(10)->create();

        $filters = new GenreFiltersDto(perPage: 3);
        $result = $this->repository->getList($filters);

        $this->assertCount(3, $result);
    }

    public function test_get_list_sorts_by_name_asc(): void
    {
        Genre::factory()->create(['name' => 'ZZZ Genre']);
        Genre::factory()->create(['name' => 'AAA Genre']);

        $filters = new GenreFiltersDto(sortBy: 'name', sortDirection: 'asc');
        $result = $this->repository->getList($filters);

        $this->assertSame('AAA Genre', $result[0]->name);
        $this->assertSame('ZZZ Genre', $result[1]->name);
    }

    public function test_get_list_sorts_by_name_desc(): void
    {
        Genre::factory()->create(['name' => 'AAA Genre']);
        Genre::factory()->create(['name' => 'ZZZ Genre']);

        $filters = new GenreFiltersDto(sortBy: 'name', sortDirection: 'desc');
        $result = $this->repository->getList($filters);

        $this->assertSame('ZZZ Genre', $result[0]->name);
        $this->assertSame('AAA Genre', $result[1]->name);
    }

    // -----------------------------------------------------------------------
    // getById
    // -----------------------------------------------------------------------

    public function test_get_by_id_returns_genre_response_dto(): void
    {
        $genre = Genre::factory()->create(['name' => 'Horror']);

        $result = $this->repository->getById($genre->id);

        $this->assertInstanceOf(GenreResponseDto::class, $result);
        $this->assertSame($genre->id, $result->id);
        $this->assertSame('Horror', $result->name);
    }

    public function test_get_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->getById(99999);

        $this->assertNull($result);
    }

    // -----------------------------------------------------------------------
    // create
    // -----------------------------------------------------------------------

    public function test_create_persists_genre_and_returns_dto(): void
    {
        $dto = $this->makeDto(['name' => 'Mystery', 'slug' => 'mystery']);

        $result = $this->repository->create($dto);

        $this->assertInstanceOf(GenreResponseDto::class, $result);
        $this->assertSame('Mystery', $result->name);
        $this->assertDatabaseHas('genres', ['name' => 'Mystery']);
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
            'name' => 'Thriller',
            'slug' => 'thriller',
            'description' => 'Suspenseful and exciting stories.',
        ]);

        $result = $this->repository->create($dto);

        $this->assertSame('Thriller', $result->name);
        $this->assertSame('thriller', $result->slug);
        $this->assertSame('Suspenseful and exciting stories.', $result->description);
    }

    // -----------------------------------------------------------------------
    // update
    // -----------------------------------------------------------------------

    public function test_update_changes_genre_fields_and_returns_dto(): void
    {
        $genre = Genre::factory()->create(['name' => 'Old Name']);
        $dto = $this->makeDto(['name' => 'New Name', 'slug' => 'new-name']);

        $result = $this->repository->update($genre->id, $dto);

        $this->assertInstanceOf(GenreResponseDto::class, $result);
        $this->assertSame('New Name', $result->name);
        $this->assertDatabaseHas('genres', ['id' => $genre->id, 'name' => 'New Name']);
    }

    public function test_update_does_not_create_new_record(): void
    {
        $genre = Genre::factory()->create();
        $dto = $this->makeDto(['name' => 'Updated', 'slug' => 'updated']);

        $this->repository->update($genre->id, $dto);

        $this->assertDatabaseCount('genres', 1);
    }

    public function test_update_returns_null_for_nonexistent_genre(): void
    {
        $dto = $this->makeDto();

        $this->expectException(ModelNotFoundException::class);

        $this->repository->update(99999, $dto);
    }

    // -----------------------------------------------------------------------
    // delete
    // -----------------------------------------------------------------------

    public function test_delete_removes_genre_from_database(): void
    {
        $genre = Genre::factory()->create();

        $result = $this->repository->delete($genre->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
    }

    public function test_delete_returns_true_on_success(): void
    {
        $genre = Genre::factory()->create();

        $result = $this->repository->delete($genre->id);

        $this->assertTrue($result);
    }

    public function test_delete_returns_false_for_nonexistent_genre(): void
    {
        $result = $this->repository->delete(99999);

        $this->assertFalse($result);
    }
}
