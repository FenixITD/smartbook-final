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

/**
 * @internal
 *
 * @coversNothing
 */
final class GenreRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private GenreRepository $repository;

    // -----------------------------------------------------------------------
    // getList
    // -----------------------------------------------------------------------

    public function testGetListReturnsArrayOfGenreResponseDtos(): void
    {
        Genre::factory()->count(3)->create();

        $filters = new GenreFiltersDto();
        $result = $this->repository->getList($filters);

        self::assertIsArray($result);
        self::assertCount(3, $result);
        self::assertContainsOnlyInstancesOf(GenreResponseDto::class, $result);
    }

    public function testGetListReturnsEmptyArrayWhenNoGenres(): void
    {
        $filters = new GenreFiltersDto();
        $result = $this->repository->getList($filters);

        self::assertSame([], $result);
    }

    public function testGetListFiltersBySearch(): void
    {
        Genre::factory()->create(['name' => 'Science Fiction']);
        Genre::factory()->create(['name' => 'Fantasy']);

        $filters = new GenreFiltersDto(search: 'Science');
        $result = $this->repository->getList($filters);

        self::assertCount(1, $result);
        self::assertSame('Science Fiction', $result[0]->name);
    }

    public function testGetListRespectsPerPage(): void
    {
        Genre::factory()->count(10)->create();

        $filters = new GenreFiltersDto(perPage: 3);
        $result = $this->repository->getList($filters);

        self::assertCount(3, $result);
    }

    public function testGetListSortsByNameAsc(): void
    {
        Genre::factory()->create(['name' => 'ZZZ Genre']);
        Genre::factory()->create(['name' => 'AAA Genre']);

        $filters = new GenreFiltersDto(sortBy: 'name', sortDirection: 'asc');
        $result = $this->repository->getList($filters);

        self::assertSame('AAA Genre', $result[0]->name);
        self::assertSame('ZZZ Genre', $result[1]->name);
    }

    public function testGetListSortsByNameDesc(): void
    {
        Genre::factory()->create(['name' => 'AAA Genre']);
        Genre::factory()->create(['name' => 'ZZZ Genre']);

        $filters = new GenreFiltersDto(sortBy: 'name', sortDirection: 'desc');
        $result = $this->repository->getList($filters);

        self::assertSame('ZZZ Genre', $result[0]->name);
        self::assertSame('AAA Genre', $result[1]->name);
    }

    // -----------------------------------------------------------------------
    // getById
    // -----------------------------------------------------------------------

    public function testGetByIdReturnsGenreResponseDto(): void
    {
        $genre = Genre::factory()->create(['name' => 'Horror']);

        $result = $this->repository->getById($genre->id);

        self::assertInstanceOf(GenreResponseDto::class, $result);
        self::assertSame($genre->id, $result->id);
        self::assertSame('Horror', $result->name);
    }

    public function testGetByIdReturnsNullWhenNotFound(): void
    {
        $result = $this->repository->getById(99999);

        self::assertNull($result);
    }

    // -----------------------------------------------------------------------
    // create
    // -----------------------------------------------------------------------

    public function testCreatePersistsGenreAndReturnsDto(): void
    {
        $dto = $this->makeDto(['name' => 'Mystery', 'slug' => 'mystery']);

        $result = $this->repository->create($dto);

        self::assertInstanceOf(GenreResponseDto::class, $result);
        self::assertSame('Mystery', $result->name);
        $this->assertDatabaseHas('genres', ['name' => 'Mystery']);
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
            'name' => 'Thriller',
            'slug' => 'thriller',
            'description' => 'Suspenseful and exciting stories.',
        ]);

        $result = $this->repository->create($dto);

        self::assertSame('Thriller', $result->name);
        self::assertSame('thriller', $result->slug);
        self::assertSame('Suspenseful and exciting stories.', $result->description);
    }

    // -----------------------------------------------------------------------
    // update
    // -----------------------------------------------------------------------

    public function testUpdateChangesGenreFieldsAndReturnsDto(): void
    {
        $genre = Genre::factory()->create(['name' => 'Old Name']);
        $dto = $this->makeDto(['name' => 'New Name', 'slug' => 'new-name']);

        $result = $this->repository->update($genre->id, $dto);

        self::assertInstanceOf(GenreResponseDto::class, $result);
        self::assertSame('New Name', $result->name);
        $this->assertDatabaseHas('genres', ['id' => $genre->id, 'name' => 'New Name']);
    }

    public function testUpdateDoesNotCreateNewRecord(): void
    {
        $genre = Genre::factory()->create();
        $dto = $this->makeDto(['name' => 'Updated', 'slug' => 'updated']);

        $this->repository->update($genre->id, $dto);

        $this->assertDatabaseCount('genres', 1);
    }

    public function testUpdateReturnsNullForNonexistentGenre(): void
    {
        $dto = $this->makeDto();

        $this->expectException(ModelNotFoundException::class);

        $this->repository->update(99999, $dto);
    }

    // -----------------------------------------------------------------------
    // delete
    // -----------------------------------------------------------------------

    public function testDeleteRemovesGenreFromDatabase(): void
    {
        $genre = Genre::factory()->create();

        $result = $this->repository->delete($genre->id);

        self::assertTrue($result);
        $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
    }

    public function testDeleteReturnsTrueOnSuccess(): void
    {
        $genre = Genre::factory()->create();

        $result = $this->repository->delete($genre->id);

        self::assertTrue($result);
    }

    public function testDeleteReturnsFalseForNonexistentGenre(): void
    {
        $result = $this->repository->delete(99999);

        self::assertFalse($result);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new GenreRepository();
    }

    /** @param array<string, mixed> $overrides */
    private function makeDto(array $overrides = []): GenreDto
    {
        return new GenreDto(
            name: (string) ($overrides['name'] ?? 'Science Fiction'),
            slug: (string) ($overrides['slug'] ?? 'science-fiction'),
            description: (string) ($overrides['description'] ?? 'A genre about science and the future.'),
        );
    }
}
