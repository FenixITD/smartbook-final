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

/**
 * @internal
 *
 * @coversNothing
 */
final class AuthorRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AuthorRepository $repository;

    // -----------------------------------------------------------------------
    // getList
    // -----------------------------------------------------------------------

    public function testGetListReturnsArrayOfAuthorResponseDtos(): void
    {
        Author::factory()->count(3)->create();

        $filters = new AuthorFiltersDto();
        $result = $this->repository->getList($filters);

        self::assertIsArray($result);
        self::assertCount(3, $result);
        self::assertContainsOnlyInstancesOf(AuthorResponseDto::class, $result);
    }

    public function testGetListReturnsEmptyArrayWhenNoAuthors(): void
    {
        $filters = new AuthorFiltersDto();
        $result = $this->repository->getList($filters);

        self::assertSame([], $result);
    }

    public function testGetListFiltersBySearch(): void
    {
        Author::factory()->create(['name' => 'Leo Tolstoy']);
        Author::factory()->create(['name' => 'Fyodor Dostoevsky']);

        $filters = new AuthorFiltersDto(search: 'Tolstoy');
        $result = $this->repository->getList($filters);

        self::assertCount(1, $result);
        self::assertSame('Leo Tolstoy', $result[0]->name);
    }

    public function testGetListRespectsPerPage(): void
    {
        Author::factory()->count(10)->create();

        $filters = new AuthorFiltersDto(perPage: 3);
        $result = $this->repository->getList($filters);

        self::assertCount(3, $result);
    }

    public function testGetListSortsByNameAsc(): void
    {
        Author::factory()->create(['name' => 'Zelda']);
        Author::factory()->create(['name' => 'Anton']);

        $filters = new AuthorFiltersDto(sortBy: 'name', sortDirection: 'asc');
        $result = $this->repository->getList($filters);

        self::assertSame('Anton', $result[0]->name);
        self::assertSame('Zelda', $result[1]->name);
    }

    public function testGetListSortsByNameDesc(): void
    {
        Author::factory()->create(['name' => 'Anton']);
        Author::factory()->create(['name' => 'Zelda']);

        $filters = new AuthorFiltersDto(sortBy: 'name', sortDirection: 'desc');
        $result = $this->repository->getList($filters);

        self::assertSame('Zelda', $result[0]->name);
        self::assertSame('Anton', $result[1]->name);
    }

    // -----------------------------------------------------------------------
    // getById
    // -----------------------------------------------------------------------

    public function testGetByIdReturnsAuthorResponseDto(): void
    {
        $author = Author::factory()->create(['name' => 'Ivan Bunin']);

        $result = $this->repository->getById($author->id);

        self::assertInstanceOf(AuthorResponseDto::class, $result);
        self::assertSame($author->id, $result->id);
        self::assertSame('Ivan Bunin', $result->name);
    }

    public function testGetByIdReturnsNullWhenNotFound(): void
    {
        $result = $this->repository->getById(99999);

        self::assertNull($result);
    }

    // -----------------------------------------------------------------------
    // create
    // -----------------------------------------------------------------------

    public function testCreatePersistsAuthorAndReturnsDto(): void
    {
        $dto = new AuthorDto(name: 'Maxim Gorky');

        $result = $this->repository->create($dto);

        self::assertInstanceOf(AuthorResponseDto::class, $result);
        self::assertSame('Maxim Gorky', $result->name);
        $this->assertDatabaseHas('authors', ['name' => 'Maxim Gorky']);
    }

    public function testCreateAssignsIdToReturnedDto(): void
    {
        $dto = new AuthorDto(name: 'Marina Tsvetaeva');

        $result = $this->repository->create($dto);

        self::assertIsInt($result->id);
        self::assertGreaterThan(0, $result->id);
    }

    // -----------------------------------------------------------------------
    // update
    // -----------------------------------------------------------------------

    public function testUpdateChangesAuthorNameAndReturnsDto(): void
    {
        $author = Author::factory()->create(['name' => 'Old Name']);
        $dto = new AuthorDto(name: 'New Name');

        $result = $this->repository->update($author->id, $dto);

        self::assertInstanceOf(AuthorResponseDto::class, $result);
        self::assertSame('New Name', $result->name);
        $this->assertDatabaseHas('authors', ['id' => $author->id, 'name' => 'New Name']);
    }

    public function testUpdateDoesNotCreateNewRecord(): void
    {
        $author = Author::factory()->create(['name' => 'Before']);
        $dto = new AuthorDto(name: 'After');

        $this->repository->update($author->id, $dto);

        $this->assertDatabaseCount('authors', 1);
    }

    // -----------------------------------------------------------------------
    // delete
    // -----------------------------------------------------------------------

    public function testDeleteRemovesAuthorFromDatabase(): void
    {
        $author = Author::factory()->create();

        $result = $this->repository->delete($author->id);

        self::assertTrue($result);
        $this->assertDatabaseMissing('authors', ['id' => $author->id]);
    }

    public function testDeleteReturnsTrueOnSuccess(): void
    {
        $author = Author::factory()->create();

        $result = $this->repository->delete($author->id);

        self::assertTrue($result);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AuthorRepository();
    }
}
