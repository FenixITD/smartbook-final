<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Dto\Favorite\FavoriteDto;
use App\Dto\Favorite\FavoriteFiltersDto;
use App\Dto\Favorite\FavoriteResponseDto;
use App\Models\Author;
use App\Models\Book;
use App\Models\Favorite;
use App\Models\User;
use App\Repositories\Eloquent\FavoriteRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class FavoriteRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private FavoriteRepository $repository;

    private User $user;

    private Book $book;

    private Author $author;

    // -----------------------------------------------------------------------
    // getList
    // -----------------------------------------------------------------------

    public function testGetListReturnsArrayOfFavoriteResponseDtos(): void
    {
        Favorite::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $result = $this->repository->getList(new FavoriteFiltersDto());

        self::assertIsArray($result);
        self::assertCount(3, $result);
        self::assertContainsOnlyInstancesOf(FavoriteResponseDto::class, $result);
    }

    public function testGetListReturnsEmptyArrayWhenNoFavorites(): void
    {
        $result = $this->repository->getList(new FavoriteFiltersDto());

        self::assertSame([], $result);
    }

    public function testGetListRespectsPerPage(): void
    {
        Favorite::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $result = $this->repository->getList(new FavoriteFiltersDto(perPage: 3));

        self::assertCount(3, $result);
    }

    public function testGetListSortsByIdAsc(): void
    {
        $f1 = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);
        $f2 = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $result = $this->repository->getList(new FavoriteFiltersDto(sortBy: 'id', sortDirection: 'asc'));

        self::assertSame($f1->id, $result[0]->id);
        self::assertSame($f2->id, $result[1]->id);
    }

    public function testGetListSortsByIdDesc(): void
    {
        $f1 = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);
        $f2 = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $result = $this->repository->getList(new FavoriteFiltersDto(sortBy: 'id', sortDirection: 'desc'));

        self::assertSame($f2->id, $result[0]->id);
        self::assertSame($f1->id, $result[1]->id);
    }

    // -----------------------------------------------------------------------
    // getById
    // -----------------------------------------------------------------------

    public function testGetByIdReturnsFavoriteResponseDto(): void
    {
        $favorite = Favorite::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $result = $this->repository->getById($favorite->id);

        self::assertInstanceOf(FavoriteResponseDto::class, $result);
        self::assertSame($favorite->id, $result->id);
        self::assertSame($this->user->id, $result->userId);
        self::assertSame($this->book->id, $result->bookId);
    }

    public function testGetByIdReturnsNullWhenNotFound(): void
    {
        $result = $this->repository->getById(99999);

        self::assertNull($result);
    }

    // -----------------------------------------------------------------------
    // create
    // -----------------------------------------------------------------------

    public function testCreatePersistsFavoriteAndReturnsDto(): void
    {
        $dto = $this->makeDto();

        $result = $this->repository->create($dto);

        self::assertInstanceOf(FavoriteResponseDto::class, $result);
        self::assertSame($this->user->id, $result->userId);
        self::assertSame($this->book->id, $result->bookId);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);
    }

    public function testCreateAssignsIdToReturnedDto(): void
    {
        $result = $this->repository->create($this->makeDto());

        self::assertIsInt($result->id);
        self::assertGreaterThan(0, $result->id);
    }

    // -----------------------------------------------------------------------
    // update
    // -----------------------------------------------------------------------

    public function testUpdateChangesFavoriteAndReturnsDto(): void
    {
        $favorite = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);
        $anotherBook = Book::factory()->create(['author_id' => $this->author->id]);
        $dto = $this->makeDto(['bookId' => $anotherBook->id]);

        $result = $this->repository->update($favorite->id, $dto);

        self::assertInstanceOf(FavoriteResponseDto::class, $result);
        self::assertSame($anotherBook->id, $result->bookId);
        $this->assertDatabaseHas('favorites', ['id' => $favorite->id, 'book_id' => $anotherBook->id]);
    }

    public function testUpdateDoesNotCreateNewRecord(): void
    {
        $favorite = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $this->repository->update($favorite->id, $this->makeDto());

        $this->assertDatabaseCount('favorites', 1);
    }

    // -----------------------------------------------------------------------
    // delete
    // -----------------------------------------------------------------------

    public function testDeleteRemovesFavoriteFromDatabase(): void
    {
        $favorite = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $result = $this->repository->delete($favorite->id);

        self::assertTrue($result);
        $this->assertDatabaseMissing('favorites', ['id' => $favorite->id]);
    }

    public function testDeleteReturnsTrueOnSuccess(): void
    {
        $favorite = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $result = $this->repository->delete($favorite->id);

        self::assertTrue($result);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new FavoriteRepository();
        $this->user = User::factory()->create();
        $this->author = Author::factory()->create();
        $this->book = Book::factory()->create(['author_id' => $this->author->id]);
    }

    /** @param array<string, mixed> $overrides */
    private function makeDto(array $overrides = []): FavoriteDto
    {
        return new FavoriteDto(
            userId: (int) ($overrides['userId'] ?? $this->user->id),
            bookId: (int) ($overrides['bookId'] ?? $this->book->id),
        );
    }
}
