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

class FavoriteRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private FavoriteRepository $repository;

    private User $user;

    private Book $book;

    private Author $author;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new FavoriteRepository;
        $this->user = User::factory()->create();
        $this->author = Author::factory()->create();
        $this->book = Book::factory()->create(['author_id' => $this->author->id]);
    }

    private function makeDto(array $overrides = []): FavoriteDto
    {
        return new FavoriteDto(
            userId: $overrides['userId'] ?? $this->user->id,
            bookId: $overrides['bookId'] ?? $this->book->id,
        );
    }

    // -----------------------------------------------------------------------
    // getList
    // -----------------------------------------------------------------------

    public function test_get_list_returns_array_of_favorite_response_dtos(): void
    {
        Favorite::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $result = $this->repository->getList(new FavoriteFiltersDto);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertContainsOnlyInstancesOf(FavoriteResponseDto::class, $result);
    }

    public function test_get_list_returns_empty_array_when_no_favorites(): void
    {
        $result = $this->repository->getList(new FavoriteFiltersDto);

        $this->assertSame([], $result);
    }

    public function test_get_list_respects_per_page(): void
    {
        Favorite::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $result = $this->repository->getList(new FavoriteFiltersDto(perPage: 3));

        $this->assertCount(3, $result);
    }

    public function test_get_list_sorts_by_id_asc(): void
    {
        $f1 = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);
        $f2 = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $result = $this->repository->getList(new FavoriteFiltersDto(sortBy: 'id', sortDirection: 'asc'));

        $this->assertSame($f1->id, $result[0]->id);
        $this->assertSame($f2->id, $result[1]->id);
    }

    public function test_get_list_sorts_by_id_desc(): void
    {
        $f1 = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);
        $f2 = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $result = $this->repository->getList(new FavoriteFiltersDto(sortBy: 'id', sortDirection: 'desc'));

        $this->assertSame($f2->id, $result[0]->id);
        $this->assertSame($f1->id, $result[1]->id);
    }

    // -----------------------------------------------------------------------
    // getById
    // -----------------------------------------------------------------------

    public function test_get_by_id_returns_favorite_response_dto(): void
    {
        $favorite = Favorite::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $result = $this->repository->getById($favorite->id);

        $this->assertInstanceOf(FavoriteResponseDto::class, $result);
        $this->assertSame($favorite->id, $result->id);
        $this->assertSame($this->user->id, $result->userId);
        $this->assertSame($this->book->id, $result->bookId);
    }

    public function test_get_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->getById(99999);

        $this->assertNull($result);
    }

    // -----------------------------------------------------------------------
    // create
    // -----------------------------------------------------------------------

    public function test_create_persists_favorite_and_returns_dto(): void
    {
        $dto = $this->makeDto();

        $result = $this->repository->create($dto);

        $this->assertInstanceOf(FavoriteResponseDto::class, $result);
        $this->assertSame($this->user->id, $result->userId);
        $this->assertSame($this->book->id, $result->bookId);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);
    }

    public function test_create_assigns_id_to_returned_dto(): void
    {
        $result = $this->repository->create($this->makeDto());

        $this->assertIsInt($result->id);
        $this->assertGreaterThan(0, $result->id);
    }

    // -----------------------------------------------------------------------
    // update
    // -----------------------------------------------------------------------

    public function test_update_changes_favorite_and_returns_dto(): void
    {
        $favorite = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);
        $anotherBook = Book::factory()->create(['author_id' => $this->author->id]);
        $dto = $this->makeDto(['bookId' => $anotherBook->id]);

        $result = $this->repository->update($favorite, $dto);

        $this->assertInstanceOf(FavoriteResponseDto::class, $result);
        $this->assertSame($anotherBook->id, $result->bookId);
        $this->assertDatabaseHas('favorites', ['id' => $favorite->id, 'book_id' => $anotherBook->id]);
    }

    public function test_update_does_not_create_new_record(): void
    {
        $favorite = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $this->repository->update($favorite, $this->makeDto());

        $this->assertDatabaseCount('favorites', 1);
    }

    // -----------------------------------------------------------------------
    // delete
    // -----------------------------------------------------------------------

    public function test_delete_removes_favorite_from_database(): void
    {
        $favorite = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $result = $this->repository->delete($favorite);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('favorites', ['id' => $favorite->id]);
    }

    public function test_delete_returns_true_on_success(): void
    {
        $favorite = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $result = $this->repository->delete($favorite);

        $this->assertTrue($result);
    }
}
