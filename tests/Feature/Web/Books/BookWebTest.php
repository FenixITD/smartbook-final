<?php declare(strict_types=1);

namespace Tests\Feature\Web\Books;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use App\Services\Book\SearchBookByQueryService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

final class BookWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (class_exists(ValidateCsrfToken::class)) {
            $this->withoutMiddleware(ValidateCsrfToken::class);
        }
        if (class_exists(VerifyCsrfToken::class)) {
            $this->withoutMiddleware(VerifyCsrfToken::class);
        }
    }

    public function test_admin_can_view_books_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = Author::factory()->create();
        Book::factory()->count(3)->create(['author_id' => $author->id]);

        $response = $this->actingAs($admin)->get('/books');

        $response->assertStatus(200)->assertViewIs('books.list');
    }

    public function test_admin_can_search_web_books_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'title' => 'Web Searchable']);

        $mock = Mockery::mock(SearchBookByQueryService::class);
        $mock->shouldReceive('searchPaginated')->andReturn([[$book->id], 1]);
        $this->app->instance(SearchBookByQueryService::class, $mock);

        $response = $this->actingAs($admin)->get('/books?search=Web');

        $response->assertStatus(200)->assertViewIs('books.list');
    }

    public function test_admin_can_view_book_create_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/books/create');

        $response->assertStatus(200)->assertViewIs('books.create');
    }

    public function test_admin_can_store_book(): void
    {
        Storage::fake('s3');
        $admin = User::factory()->create(['role' => 'admin']);
        $author = Author::factory()->create();
        $genre = Genre::factory()->create();
        $file = UploadedFile::fake()->image('cover.jpg');

        $response = $this->actingAs($admin)->post('/books', [
            'title' => 'Web Book',
            'slug' => 'web-book',
            'authorId' => $author->id,
            'description' => 'Book Desc',
            'price' => 20.00,
            'stock' => 10,
            'status' => 'active',
            'publishYear' => 2024,
            'cover_image' => $file,
            'genres' => [$genre->id],
        ]);

        $response->assertRedirect(route('books.index'));
        $this->assertDatabaseHas('books', ['title' => 'Web Book']);
    }

    public function test_admin_can_view_book_edit_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        $response = $this->actingAs($admin)->get("/books/{$book->id}/edit");

        $response->assertStatus(200)->assertViewIs('books.edit');
    }

    public function test_admin_can_update_book(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);
        $genre = Genre::factory()->create();

        $response = $this->actingAs($admin)->put("/books/{$book->id}", [
            'title' => 'Updated Web Book',
            'slug' => $book->slug,
            'authorId' => $author->id,
            'description' => $book->description,
            'price' => 25.00,
            'stock' => 15,
            'status' => 'active',
            'publishYear' => 2025,
            'genres' => [$genre->id],
        ]);

        $response->assertRedirect(route('books.index'));
        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'Updated Web Book']);
    }

    public function test_admin_can_delete_book(): void
    {
        Storage::fake('s3');
        $admin = User::factory()->create(['role' => 'admin']);
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'cover_image' => 'covers/test.jpg']);

        $response = $this->actingAs($admin)->delete("/books/{$book->id}");

        $response->assertRedirect(route('books.index'));
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_admin_can_view_single_book(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        $response = $this->actingAs($admin)->get("/books/{$book->id}");

        $response->assertStatus(200)->assertViewIs('books.show');
    }
}
