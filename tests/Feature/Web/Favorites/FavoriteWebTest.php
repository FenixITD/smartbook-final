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

    private function createFavorites(User $user, int $count): \Illuminate\Support\Collection
    {
        $author = Author::factory()->create();
        $books = Book::factory()->count($count)->create(['author_id' => $author->id]);
        $now = now();

        Favorite::insert($books->map(static fn (Book $book): array => [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all());

        return $books;
    }

    public function test_favorites_pagination_works_beyond_hundred_entries(): void
    {
        $user = User::factory()->create();
        $books = $this->createFavorites($user, 150);

        $response = $this->actingAs($user)->get('/favorites?page=9');

        $response->assertStatus(200);
        $pageBookIds = collect($response->viewData('books')->items)->pluck('id')->all();
        $this->assertNotEmpty($pageBookIds);
        foreach ($pageBookIds as $bookId) {
            $this->assertContains($bookId, $books->pluck('id'));
        }
    }

    public function test_dashboard_highlights_all_favorites_beyond_hundred(): void
    {
        $user = User::factory()->create();
        $books = $this->createFavorites($user, 150);

        $emptyPage = new \App\Dto\PaginatedResponseDto(items: [], total: 0, perPage: 12, currentPage: 1, lastPage: 1);
        $dashboardMock = \Mockery::mock(\App\Services\Book\GetDashboardBooksService::class);
        $dashboardMock->shouldReceive('get')->once()->andReturn($emptyPage);
        $this->instance(\App\Services\Book\GetDashboardBooksService::class, $dashboardMock);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $favoriteBookIds = $response->viewData('favoriteBookIds');
        $this->assertCount(150, $favoriteBookIds);
        foreach ($books as $book) {
            $this->assertContains($book->id, $favoriteBookIds);
        }
    }
}
