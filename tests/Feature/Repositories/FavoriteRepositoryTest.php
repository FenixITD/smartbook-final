<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Dto\Favorite\FavoriteDto;
use App\Dto\Favorite\FavoriteFiltersDto;
use App\Dto\Favorite\FavoriteResponseDto;
use App\Models\Author;
use App\Models\Book;
use App\Models\Favorite;
use App\Models\User;
use App\Repositories\Eloquent\FavoriteRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private FavoriteRepository $repository;
    private User $user;
    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new FavoriteRepository();

        $author = Author::create(['name' => 'Test Author']);

        $this->user = User::factory()->create();

        $this->book = Book::create([
            'title' => 'Test Book',
            'slug' => 'test-book',
            'author_id' => $author->id,
            'description' => 'Test description',
            'price' => 9.99,
            'stock' => 10,
            'publish_year' => 2024,
            'cover_image' => null,
            'average_rating' => 0,
            'ratings_count' => 0,
            'status' => 'active',
        ]);
    }

    private function createBook(string $slug = 'another-book'): Book
    {
        return Book::create([
            'title' => 'Another Book',
            'slug' => $slug,
            'author_id' => $this->book->author_id,
            'description' => 'Another description',
            'price' => 14.99,
            'stock' => 5,
            'publish_year' => 2023,
            'cover_image' => null,
            'average_rating' => 0,
            'ratings_count' => 0,
            'status' => 'active',
        ]);
    }

    public function test_get_list_returns_empty_array_when_no_favorites(): void
    {
        $filters = new FavoriteFiltersDto();

        $result = $this->repository->getList($filters);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_get_list_returns_array_of_favorite_response_dtos(): void
    {
        Favorite::create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $filters = new FavoriteFiltersDto();

        $result = $this->repository->getList($filters);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(FavoriteResponseDto::class, $result[0]);
    }

    public function test_get_list_filters_by_id(): void
    {
        $favorite1 = Favorite::create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $user2 = User::factory()->create();
        $book2 = $this->createBook('book-slug-2');
        Favorite::create(['user_id' => $user2->id, 'book_id' => $book2->id]);

        $filters = new FavoriteFiltersDto(id: $favorite1->id);

        $result = $this->repository->getList($filters);

        $this->assertCount(1, $result);
        $this->assertEquals($favorite1->id, $result[0]->id);
    }

    public function test_get_list_respects_sort_direction(): void
    {
        $user2 = User::factory()->create();
        $book2 = $this->createBook('book-slug-3');

        $favorite1 = Favorite::create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);
        $favorite2 = Favorite::create(['user_id' => $user2->id, 'book_id' => $book2->id]);

        $filtersAsc = new FavoriteFiltersDto(sortBy: 'id', sortDirection: 'asc');
        $resultAsc = $this->repository->getList($filtersAsc);

        $this->assertEquals($favorite1->id, $resultAsc[0]->id);
        $this->assertEquals($favorite2->id, $resultAsc[1]->id);

        $filtersDesc = new FavoriteFiltersDto(sortBy: 'id', sortDirection: 'desc');
        $resultDesc = $this->repository->getList($filtersDesc);

        $this->assertEquals($favorite2->id, $resultDesc[0]->id);
        $this->assertEquals($favorite1->id, $resultDesc[1]->id);
    }

    public function test_get_list_respects_per_page(): void
    {
        $users = User::factory()->count(5)->create();
        $books = [];
        foreach (range(1, 5) as $i) {
            $books[] = $this->createBook('book-slug-pp-' . $i);
        }

        foreach ($users as $index => $user) {
            Favorite::create(['user_id' => $user->id, 'book_id' => $books[$index]->id]);
        }

        $filters = new FavoriteFiltersDto(perPage: 2);

        $result = $this->repository->getList($filters);

        $this->assertCount(2, $result);
    }

    public function test_get_by_id_returns_favorite_response_dto(): void
    {
        $favorite = Favorite::create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $result = $this->repository->getById($favorite->id);

        $this->assertInstanceOf(FavoriteResponseDto::class, $result);
        $this->assertEquals($favorite->id, $result->id);
        $this->assertEquals($this->user->id, $result->userId);
        $this->assertEquals($this->book->id, $result->bookId);
    }

    public function test_get_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->getById(99999);

        $this->assertNull($result);
    }

    public function test_create_saves_favorite_to_database(): void
    {
        $dto = new FavoriteDto(userId: $this->user->id, bookId: $this->book->id);

        $result = $this->repository->create($dto);

        $this->assertInstanceOf(FavoriteResponseDto::class, $result);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);
    }

    public function test_create_returns_dto_with_correct_data(): void
    {
        $dto = new FavoriteDto(userId: $this->user->id, bookId: $this->book->id);

        $result = $this->repository->create($dto);

        $this->assertEquals($this->user->id, $result->userId);
        $this->assertEquals($this->book->id, $result->bookId);
        $this->assertNotEmpty($result->createdAt);
        $this->assertNotEmpty($result->updatedAt);
    }

    public function test_create_throws_exception_on_duplicate(): void
    {
        Favorite::create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $this->expectException(QueryException::class);

        $dto = new FavoriteDto(userId: $this->user->id, bookId: $this->book->id);
        $this->repository->create($dto);
    }

    public function test_update_changes_favorite_in_database(): void
    {
        $favorite = Favorite::create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $user2 = User::factory()->create();
        $book2 = $this->createBook('book-for-update');

        $dto = new FavoriteDto(userId: $user2->id, bookId: $book2->id);

        $result = $this->repository->update($favorite->id, $dto);

        $this->assertInstanceOf(FavoriteResponseDto::class, $result);
        $this->assertEquals($user2->id, $result->userId);
        $this->assertEquals($book2->id, $result->bookId);
        $this->assertDatabaseHas('favorites', [
            'id' => $favorite->id,
            'user_id' => $user2->id,
            'book_id' => $book2->id,
        ]);
    }

    public function test_update_throws_exception_when_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $dto = new FavoriteDto(userId: $this->user->id, bookId: $this->book->id);
        $this->repository->update(99999, $dto);
    }

    public function test_delete_removes_favorite_from_database(): void
    {
        $favorite = Favorite::create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $result = $this->repository->delete($favorite->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('favorites', ['id' => $favorite->id]);
    }

    public function test_delete_throws_exception_when_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->delete(99999);
    }

    public function test_toggle_creates_favorite_when_not_exists(): void
    {
        $result = $this->repository->toggle($this->user->id, $this->book->id);

        $this->assertTrue($result);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);
    }

    public function test_toggle_deletes_favorite_when_exists(): void
    {
        Favorite::create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $result = $this->repository->toggle($this->user->id, $this->book->id);

        $this->assertFalse($result);
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);
    }

    public function test_toggle_returns_true_on_add_and_false_on_remove(): void
    {
        $addResult = $this->repository->toggle($this->user->id, $this->book->id);
        $this->assertTrue($addResult);

        $removeResult = $this->repository->toggle($this->user->id, $this->book->id);
        $this->assertFalse($removeResult);

        $addAgainResult = $this->repository->toggle($this->user->id, $this->book->id);
        $this->assertTrue($addAgainResult);
    }

    public function test_get_book_ids_by_user_returns_empty_array_when_no_favorites(): void
    {
        $result = $this->repository->getBookIdsByUser($this->user->id);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_get_book_ids_by_user_returns_correct_ids(): void
    {
        $book2 = $this->createBook('book-ids-test');

        Favorite::create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);
        Favorite::create(['user_id' => $this->user->id, 'book_id' => $book2->id]);

        $result = $this->repository->getBookIdsByUser($this->user->id);

        $this->assertCount(2, $result);
        $this->assertContains($this->book->id, $result);
        $this->assertContains($book2->id, $result);
    }

    public function test_get_book_ids_by_user_returns_all_ids_beyond_hundred(): void
    {
        $author = Author::factory()->create();
        $books = Book::factory()->count(150)->create(['author_id' => $author->id]);
        $now = now();

        Favorite::insert($books->map(fn (Book $book): array => [
            'user_id' => $this->user->id,
            'book_id' => $book->id,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all());

        $result = $this->repository->getBookIdsByUser($this->user->id);

        $this->assertCount(150, $result);
        foreach ($books as $book) {
            $this->assertContains($book->id, $result);
        }
    }

    public function test_get_book_ids_by_user_does_not_return_other_users_favorites(): void
    {
        $user2 = User::factory()->create();
        $book2 = $this->createBook('book-other-user');

        Favorite::create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);
        Favorite::create(['user_id' => $user2->id, 'book_id' => $book2->id]);

        $result = $this->repository->getBookIdsByUser($this->user->id);

        $this->assertCount(1, $result);
        $this->assertContains($this->book->id, $result);
        $this->assertNotContains($book2->id, $result);
    }

    public function test_get_book_ids_by_user_returns_array_of_integers(): void
    {
        Favorite::create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $result = $this->repository->getBookIdsByUser($this->user->id);

        $this->assertContainsOnly('int', $result);
    }
}
