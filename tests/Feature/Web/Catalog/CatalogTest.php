<?php declare(strict_types=1);

namespace Tests\Feature\Web\Catalog;

use App\Models\Author;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_view_book_in_catalog(): void
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'slug' => 'test-book']);
        $user = User::factory()->create();
        Review::factory()->create(['book_id' => $book->id, 'user_id' => $user->id]);

        $response = $this->get("/catalog/{$book->slug}");

        $response->assertStatus(200)->assertViewIs('catalog.show');
    }

    public function test_authenticated_user_can_view_catalog_book_with_user_review(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'slug' => 'test-book']);
        Review::factory()->create(['book_id' => $book->id, 'user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/catalog/{$book->slug}");

        $response->assertStatus(200)->assertViewIs('catalog.show');
    }
}
