<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Dto\Author\AuthorDto;
use App\Dto\Author\AuthorFiltersDto;
use App\Dto\Author\AuthorResponseDto;
use App\Models\Author;
use App\Repositories\Eloquent\AuthorRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AuthorRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AuthorRepository;
    }

    // -----------------------------------------------------------------------
    // getList
    // -----------------------------------------------------------------------

    public function test_get_list_returns_array_of_author_response_dtos(): void
    {
        Author::factory()->count(3)->create();

        $filters = new AuthorFiltersDto;
        $result = $this->repository->getList($filters);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertContainsOnlyInstancesOf(AuthorResponseDto::class, $result);
    }

    public function test_get_list_returns_empty_array_when_no_authors(): void
    {
        $filters = new AuthorFiltersDto;
        $result = $this->repository->getList($filters);

        $this->assertSame([], $result);
    }

    public function test_get_list_filters_by_search(): void
    {
        Author::factory()->create(['name' => 'Leo Tolstoy']);
        Author::factory()->create(['name' => 'Fyodor Dostoevsky']);

        $filters = new AuthorFiltersDto(search: 'Tolstoy');
        $result = $this->repository->getList($filters);

        $this->assertCount(1, $result);
        $this->assertSame('Leo Tolstoy', $result[0]->name);
    }

    public function test_get_list_respects_per_page(): void
    {
        Author::factory()->count(10)->create();

        $filters = new AuthorFiltersDto(perPage: 3);
        $result = $this->repository->getList($filters);

        $this->assertCount(3, $result);
    }

    public function test_get_list_sorts_by_name_asc(): void
    {
        Author::factory()->create(['name' => 'Zelda']);
        Author::factory()->create(['name' => 'Anton']);

        $filters = new AuthorFiltersDto(sortBy: 'name', sortDirection: 'asc');
        $result = $this->repository->getList($filters);

        $this->assertSame('Anton', $result[0]->name);
        $this->assertSame('Zelda', $result[1]->name);
    }

    public function test_get_list_sorts_by_name_desc(): void
    {
        Author::factory()->create(['name' => 'Anton']);
        Author::factory()->create(['name' => 'Zelda']);

        $filters = new AuthorFiltersDto(sortBy: 'name', sortDirection: 'desc');
        $result = $this->repository->getList($filters);

        $this->assertSame('Zelda', $result[0]->name);
        $this->assertSame('Anton', $result[1]->name);
    }

    // -----------------------------------------------------------------------
    // getById
    // -----------------------------------------------------------------------

    public function test_get_by_id_returns_author_response_dto(): void
    {
        $author = Author::factory()->create(['name' => 'Ivan Bunin']);

        $result = $this->repository->getById($author->id);

        $this->assertInstanceOf(AuthorResponseDto::class, $result);
        $this->assertSame($author->id, $result->id);
        $this->assertSame('Ivan Bunin', $result->name);
    }

    public function test_get_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->getById(99999);

        $this->assertNull($result);
    }

    // -----------------------------------------------------------------------
    // create
    // -----------------------------------------------------------------------

    public function test_create_persists_author_and_returns_dto(): void
    {
        $dto = new AuthorDto(name: 'Maxim Gorky');

        $result = $this->repository->create($dto);

        $this->assertInstanceOf(AuthorResponseDto::class, $result);
        $this->assertSame('Maxim Gorky', $result->name);
        $this->assertDatabaseHas('authors', ['name' => 'Maxim Gorky']);
    }

    public function test_create_assigns_id_to_returned_dto(): void
    {
        $dto = new AuthorDto(name: 'Marina Tsvetaeva');

        $result = $this->repository->create($dto);

        $this->assertIsInt($result->id);
        $this->assertGreaterThan(0, $result->id);
    }

    // -----------------------------------------------------------------------
    // update
    // -----------------------------------------------------------------------

    public function test_update_changes_author_name_and_returns_dto(): void
    {
        $author = Author::factory()->create(['name' => 'Old Name']);
        $dto = new AuthorDto(name: 'New Name');

        $result = $this->repository->update($author, $dto);

        $this->assertInstanceOf(AuthorResponseDto::class, $result);
        $this->assertSame('New Name', $result->name);
        $this->assertDatabaseHas('authors', ['id' => $author->id, 'name' => 'New Name']);
    }

    public function test_update_does_not_create_new_record(): void
    {
        $author = Author::factory()->create(['name' => 'Before']);
        $dto = new AuthorDto(name: 'After');

        $this->repository->update($author, $dto);

        $this->assertDatabaseCount('authors', 1);
    }

    // -----------------------------------------------------------------------
    // delete
    // -----------------------------------------------------------------------

    public function test_delete_removes_author_from_database(): void
    {
        $author = Author::factory()->create();

        $result = $this->repository->delete($author);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('authors', ['id' => $author->id]);
    }

    public function test_delete_returns_true_on_success(): void
    {
        $author = Author::factory()->create();

        $result = $this->repository->delete($author);

        $this->assertTrue($result);
    }
}
