<?php declare(strict_types=1);

namespace Tests\Feature\Api\Favorites;

use App\Models\Author;
use App\Models\Book;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class FavoriteApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_favorites(): void
    {
        $response = $this->getJson('/api/favorites');
        $response->assertStatus(401);
    }

    public function test_non_admin_cannot_create_favorite(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/favorites', [
            'userId' => $user->id,
            'bookId' => $book->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_get_favorites_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $books = Book::factory()->count(3)->create(['author_id' => $author->id]);

        foreach ($books as $b) {
            Favorite::insert([
                'user_id' => $user->id,
                'book_id' => $b->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/favorites');

        $response->assertStatus(200)->assertJsonStructure(['data']);
    }

    public function test_admin_can_get_single_favorite(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        Favorite::insert(['user_id' => $user->id, 'book_id' => $book->id]);
        $favorite = Favorite::first();

        $response = $this->actingAs($admin, 'sanctum')->getJson("/api/favorites/{$favorite->id}");

        $response->assertStatus(200)->assertJsonPath('data.id', $favorite->id);
    }

    public function test_admin_cannot_create_favorite_with_invalid_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/favorites', [
            'userId' => 9999,
            'bookId' => 9999,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['userId', 'bookId']);
    }

    public function test_admin_can_create_favorite(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/favorites', [
            'userId' => $user->id,
            'bookId' => $book->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.userId', $user->id)
            ->assertJsonPath('data.bookId', $book->id);
    }

    public function test_admin_can_update_favorite(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        Favorite::insert(['user_id' => $user1->id, 'book_id' => $book->id]);
        $favorite = Favorite::first();

        $response = $this->actingAs($admin, 'sanctum')->putJson("/api/favorites/{$favorite->id}", [
            'userId' => $user2->id,
            'bookId' => $book->id,
        ]);

        $response->assertStatus(200)->assertJsonPath('data.userId', $user2->id);
    }

    public function test_admin_can_delete_favorite(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        Favorite::insert(['user_id' => $user->id, 'book_id' => $book->id]);
        $favorite = Favorite::first();

        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/favorites/{$favorite->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('favorites', ['id' => $favorite->id]);
    }
}
