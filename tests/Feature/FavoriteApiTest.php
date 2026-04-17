<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class FavoriteApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Book $book;

    private Author $author;

    // -----------------------------------------------------------------------
    // GET /api/favorites
    // -----------------------------------------------------------------------

    public function testGetListReturns200WithFavorites(): void
    {
        Favorite::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->getJson('/api/favorites');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'userId', 'bookId', 'createdAt', 'updatedAt'],
                ],
            ]);
    }

    public function testGetListReturnsEmptyDataWhenNoFavorites(): void
    {
        $response = $this->getJson('/api/favorites');

        $response->assertStatus(200)
            ->assertJson(['data' => []]);
    }

    public function testGetListRespectsPerPageParam(): void
    {
        Favorite::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->getJson('/api/favorites?perPage=3');

        $response->assertStatus(200);
        self::assertCount(3, $response->json('data'));
    }

    public function testGetListValidatesSortDirection(): void
    {
        $response = $this->getJson('/api/favorites?sortDirection=invalid');

        $response->assertStatus(422);
    }

    public function testGetListValidatesPerPageMin(): void
    {
        $response = $this->getJson('/api/favorites?perPage=0');

        $response->assertStatus(422);
    }

    public function testGetListValidatesPerPageMax(): void
    {
        $response = $this->getJson('/api/favorites?perPage=101');

        $response->assertStatus(422);
    }

    // -----------------------------------------------------------------------
    // GET /api/favorites/{favorite}
    // -----------------------------------------------------------------------

    public function testGetByIdReturnsFavorite(): void
    {
        $favorite = Favorite::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->getJson("/api/favorites/{$favorite->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'userId', 'bookId', 'createdAt', 'updatedAt'],
            ])
            ->assertJsonPath('data.id', $favorite->id)
            ->assertJsonPath('data.userId', $this->user->id)
            ->assertJsonPath('data.bookId', $this->book->id);
    }

    public function testGetByIdReturns404ForNonexistentFavorite(): void
    {
        $response = $this->getJson('/api/favorites/99999');

        $response->assertStatus(404);
    }

    // -----------------------------------------------------------------------
    // POST /api/favorites
    // -----------------------------------------------------------------------

    public function testCreateFavoriteReturns201WithData(): void
    {
        $response = $this->postJson('/api/favorites', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'userId', 'bookId', 'createdAt', 'updatedAt'],
            ])
            ->assertJsonPath('data.userId', $this->user->id)
            ->assertJsonPath('data.bookId', $this->book->id);
    }

    public function testCreateFavoritePersistsToDatabase(): void
    {
        $this->postJson('/api/favorites', $this->validPayload());

        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);
    }

    public function testCreateFavoriteRequiresUserId(): void
    {
        $response = $this->postJson('/api/favorites', $this->validPayload(['userId' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['userId']);
    }

    public function testCreateFavoriteRequiresValidUserId(): void
    {
        $response = $this->postJson('/api/favorites', $this->validPayload(['userId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['userId']);
    }

    public function testCreateFavoriteRequiresValidBookId(): void
    {
        $response = $this->postJson('/api/favorites', $this->validPayload(['bookId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bookId']);
    }

    public function testCreateFavoriteRequiresBookId(): void
    {
        $response = $this->postJson('/api/favorites', $this->validPayload(['bookId' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bookId']);
    }

    // -----------------------------------------------------------------------
    // PUT /api/favorites/{favorite}
    // -----------------------------------------------------------------------

    public function testUpdateFavoriteReturns200WithUpdatedData(): void
    {
        $favorite = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);
        $anotherBook = Book::factory()->create(['author_id' => $this->author->id]);

        $response = $this->putJson("/api/favorites/{$favorite->id}", $this->validPayload(['bookId' => $anotherBook->id]));

        $response->assertStatus(200)
            ->assertJsonPath('data.bookId', $anotherBook->id);
    }

    public function testUpdateFavoritePersistsChangesToDatabase(): void
    {
        $favorite = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);
        $anotherBook = Book::factory()->create(['author_id' => $this->author->id]);

        $this->putJson("/api/favorites/{$favorite->id}", $this->validPayload(['bookId' => $anotherBook->id]));

        $this->assertDatabaseHas('favorites', [
            'id' => $favorite->id,
            'book_id' => $anotherBook->id,
        ]);
    }

    public function testUpdateFavoriteReturns404ForNonexistentFavorite(): void
    {
        $response = $this->putJson('/api/favorites/99999', $this->validPayload());

        $response->assertStatus(404);
    }

    public function testUpdateFavoriteRequiresUserId(): void
    {
        $favorite = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $response = $this->putJson("/api/favorites/{$favorite->id}", $this->validPayload(['userId' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['userId']);
    }

    public function testUpdateFavoriteRequiresBookId(): void
    {
        $favorite = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $response = $this->putJson("/api/favorites/{$favorite->id}", $this->validPayload(['bookId' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bookId']);
    }

    // -----------------------------------------------------------------------
    // DELETE /api/favorites/{favorite}
    // -----------------------------------------------------------------------

    public function testDeleteFavoriteReturns200WithMessage(): void
    {
        $favorite = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $response = $this->deleteJson("/api/favorites/{$favorite->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Favorite deleted successfully']);
    }

    public function testDeleteFavoriteRemovesFromDatabase(): void
    {
        $favorite = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $this->deleteJson("/api/favorites/{$favorite->id}");

        $this->assertDatabaseMissing('favorites', ['id' => $favorite->id]);
    }

    public function testDeleteFavoriteReturns404ForNonexistentFavorite(): void
    {
        $response = $this->deleteJson('/api/favorites/99999');

        $response->assertStatus(404);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->author = Author::factory()->create();
        $this->book = Book::factory()->create(['author_id' => $this->author->id]);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'userId' => $this->user->id,
            'bookId' => $this->book->id,
        ], $overrides);
    }
}
