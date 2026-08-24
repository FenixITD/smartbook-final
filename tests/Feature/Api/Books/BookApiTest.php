<?php declare(strict_types=1);

namespace Tests\Feature\Api\Books;

use App\Models\Author;
use App\Models\Book;
use App\Models\User;
use App\Services\Book\SearchSuggestBookService;
use App\Services\Book\SearchSuggestCatalogBookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

final class BookApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_get_books_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = Author::factory()->create();
        Book::factory()->count(3)->create(['author_id' => $author->id]);

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/books');

        $response->assertStatus(200)->assertJsonStructure(['data']);
    }

    public function test_admin_can_get_single_book(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        $response = $this->actingAs($admin, 'sanctum')->getJson("/api/books/{$book->id}");

        $response->assertStatus(200)->assertJsonPath('data.id', $book->id);
    }

    public function test_admin_can_create_book(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = Author::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/books', [
            'title' => 'API Book',
            'slug' => 'api-book',
            'authorId' => $author->id,
            'description' => 'Book Description',
            'price' => 10.99,
            'stock' => 5,
            'status' => 'active',
            'publishYear' => 2023,
        ]);

        $response->assertStatus(201)->assertJsonPath('data.title', 'API Book');
    }

    public function test_admin_can_update_book(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'title' => 'Old Title']);

        $response = $this->actingAs($admin, 'sanctum')->putJson("/api/books/{$book->id}", [
            'title' => 'New Title',
            'slug' => $book->slug,
            'authorId' => $author->id,
            'description' => $book->description,
            'price' => 15.00,
            'stock' => 10,
            'status' => 'active',
            'publishYear' => 2024,
        ]);

        $response->assertStatus(200)->assertJsonPath('data.title', 'New Title');
    }

    public function test_admin_can_delete_book(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = Author::factory()->create();
        $user = User::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);
        $review = \App\Models\Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/books/{$book->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_search_suggest_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'title' => 'Harry Potter']);

        $mock = Mockery::mock(SearchSuggestBookService::class);
        $mock->shouldReceive('execute')->withAnyArgs()->andReturn([
            ['id' => $book->id, 'title' => 'Harry Potter', 'author' => 'Author', 'url' => 'http://localhost/books/1']
        ]);
        $this->app->instance(SearchSuggestBookService::class, $mock);

        $response = $this->actingAs($admin, 'sanctum')->getJson(route('api.books.suggest', ['q' => 'harry']));

        $response->assertStatus(200)->assertJsonPath('0.title', 'Harry Potter');
    }

    public function test_catalog_search_suggest(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'title' => 'Harry Potter']);

        $mock = Mockery::mock(SearchSuggestCatalogBookService::class);
        $mock->shouldReceive('execute')->withAnyArgs()->andReturn([
            ['id' => $book->id, 'title' => 'Harry Potter', 'author' => 'Author', 'cover_image' => null, 'price' => 10, 'url' => 'http://localhost/catalog/1']
        ]);
        $this->app->instance(SearchSuggestCatalogBookService::class, $mock);

        $response = $this->actingAs($admin, 'sanctum')->getJson(route('api.books.catalog.suggest', ['q' => 'harry']));

        $response->assertStatus(200)->assertJsonPath('0.title', 'Harry Potter');
    }

    public function test_search_suggest_validation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $mock = Mockery::mock(SearchSuggestBookService::class);
        $mock->shouldIgnoreMissing();
        $this->app->instance(SearchSuggestBookService::class, $mock);

        $response = $this->actingAs($admin, 'sanctum')->getJson(route('api.books.suggest', ['q' => '1']));

        $response->assertStatus(422)->assertJsonValidationErrors(['q']);
    }
}
