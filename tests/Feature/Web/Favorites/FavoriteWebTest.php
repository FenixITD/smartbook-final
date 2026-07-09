<?php declare(strict_types=1);

namespace Tests\Feature\Web\Favorites;

use App\Models\Author;
use App\Models\Book;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class FavoriteWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (class_exists(ValidateCsrfToken::class)) {
            $this->withoutMiddleware(ValidateCsrfToken::class);
        }
    }

    public function test_unauthenticated_user_cannot_view_favorites(): void
    {
        $response = $this->get('/favorites');
        $response->assertRedirect();
    }

    public function test_authenticated_user_can_view_favorites(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        Favorite::insert(['user_id' => $user->id, 'book_id' => $book->id, 'created_at' => now(), 'updated_at' => now()]);

        $response = $this->actingAs($user)->get('/favorites');
        $response->assertStatus(200)->assertViewIs('favorites.index');
    }

    public function test_authenticated_user_can_toggle_favorite_on(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        $response = $this->actingAs($user)->post('/favorites/toggle', [
            'book_id' => $book->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('favorites', ['user_id' => $user->id, 'book_id' => $book->id]);
    }
}
