<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Book $book;

    private Author $author;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->author = Author::factory()->create();
        $this->book = Book::factory()->create(['author_id' => $this->author->id]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'userId' => $this->user->id,
            'bookId' => $this->book->id,
        ], $overrides);
    }

    // -----------------------------------------------------------------------
    // GET /api/favorites
    // -----------------------------------------------------------------------

    public function test_get_list_returns_200_with_favorites(): void
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

    public function test_get_list_returns_empty_data_when_no_favorites(): void
    {
        $response = $this->getJson('/api/favorites');

        $response->assertStatus(200)
            ->assertJson(['data' => []]);
    }

    public function test_get_list_respects_per_page_param(): void
    {
        Favorite::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->getJson('/api/favorites?perPage=3');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_get_list_validates_sort_direction(): void
    {
        $response = $this->getJson('/api/favorites?sortDirection=invalid');

        $response->assertStatus(422);
    }

    public function test_get_list_validates_per_page_min(): void
    {
        $response = $this->getJson('/api/favorites?perPage=0');

        $response->assertStatus(422);
    }

    public function test_get_list_validates_per_page_max(): void
    {
        $response = $this->getJson('/api/favorites?perPage=101');

        $response->assertStatus(422);
    }

    // -----------------------------------------------------------------------
    // GET /api/favorites/{favorite}
    // -----------------------------------------------------------------------

    public function test_get_by_id_returns_favorite(): void
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

    public function test_get_by_id_returns_404_for_nonexistent_favorite(): void
    {
        $response = $this->getJson('/api/favorites/99999');

        $response->assertStatus(404);
    }

    // -----------------------------------------------------------------------
    // POST /api/favorites
    // -----------------------------------------------------------------------

    public function test_create_favorite_returns_201_with_data(): void
    {
        $response = $this->postJson('/api/favorites', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'userId', 'bookId', 'createdAt', 'updatedAt'],
            ])
            ->assertJsonPath('data.userId', $this->user->id)
            ->assertJsonPath('data.bookId', $this->book->id);
    }

    public function test_create_favorite_persists_to_database(): void
    {
        $this->postJson('/api/favorites', $this->validPayload());

        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);
    }

    public function test_create_favorite_requires_user_id(): void
    {
        $response = $this->postJson('/api/favorites', $this->validPayload(['userId' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['userId']);
    }

    public function test_create_favorite_requires_valid_user_id(): void
    {
        $response = $this->postJson('/api/favorites', $this->validPayload(['userId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['userId']);
    }

    public function test_create_favorite_requires_valid_book_id(): void
    {
        $response = $this->postJson('/api/favorites', $this->validPayload(['bookId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bookId']);
    }

    public function test_create_favorite_requires_book_id(): void
    {
        $response = $this->postJson('/api/favorites', $this->validPayload(['bookId' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bookId']);
    }

    // -----------------------------------------------------------------------
    // PUT /api/favorites/{favorite}
    // -----------------------------------------------------------------------

    public function test_update_favorite_returns_200_with_updated_data(): void
    {
        $favorite = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);
        $anotherBook = Book::factory()->create(['author_id' => $this->author->id]);

        $response = $this->putJson("/api/favorites/{$favorite->id}", $this->validPayload(['bookId' => $anotherBook->id]));

        $response->assertStatus(200)
            ->assertJsonPath('data.bookId', $anotherBook->id);
    }

    public function test_update_favorite_persists_changes_to_database(): void
    {
        $favorite = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);
        $anotherBook = Book::factory()->create(['author_id' => $this->author->id]);

        $this->putJson("/api/favorites/{$favorite->id}", $this->validPayload(['bookId' => $anotherBook->id]));

        $this->assertDatabaseHas('favorites', [
            'id' => $favorite->id,
            'book_id' => $anotherBook->id,
        ]);
    }

    public function test_update_favorite_returns_404_for_nonexistent_favorite(): void
    {
        $response = $this->putJson('/api/favorites/99999', $this->validPayload());

        $response->assertStatus(404);
    }

    public function test_update_favorite_requires_user_id(): void
    {
        $favorite = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $response = $this->putJson("/api/favorites/{$favorite->id}", $this->validPayload(['userId' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['userId']);
    }

    public function test_update_favorite_requires_book_id(): void
    {
        $favorite = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $response = $this->putJson("/api/favorites/{$favorite->id}", $this->validPayload(['bookId' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bookId']);
    }

    // -----------------------------------------------------------------------
    // DELETE /api/favorites/{favorite}
    // -----------------------------------------------------------------------

    public function test_delete_favorite_returns_200_with_message(): void
    {
        $favorite = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $response = $this->deleteJson("/api/favorites/{$favorite->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Favorite deleted successfully']);
    }

    public function test_delete_favorite_removes_from_database(): void
    {
        $favorite = Favorite::factory()->create(['user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $this->deleteJson("/api/favorites/{$favorite->id}");

        $this->assertDatabaseMissing('favorites', ['id' => $favorite->id]);
    }

    public function test_delete_favorite_returns_404_for_nonexistent_favorite(): void
    {
        $response = $this->deleteJson('/api/favorites/99999');

        $response->assertStatus(404);
    }
}
