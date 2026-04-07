<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    private Author $author;

    protected function setUp(): void
    {
        parent::setUp();
        $this->author = Author::factory()->create();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Clean Code',
            'slug' => 'clean-code',
            'authorId' => $this->author->id,
            'description' => 'A book about writing clean code.',
            'price' => 29.99,
            'stock' => 10,
            'publishYear' => 2008,
            'coverImage' => null,
            'averageRating' => null,
            'ratingsCount' => null,
            'status' => 'published',
        ], $overrides);
    }

    // -----------------------------------------------------------------------
    // GET /api/books
    // -----------------------------------------------------------------------

    public function test_get_list_returns_200_with_books(): void
    {
        Book::factory()->count(3)->create(['author_id' => $this->author->id]);

        $response = $this->getJson('/api/books');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'slug', 'authorId', 'description', 'price', 'stock', 'status', 'createdAt', 'updatedAt'],
                ],
            ]);
    }

    public function test_get_list_returns_empty_data_when_no_books(): void
    {
        $response = $this->getJson('/api/books');

        $response->assertStatus(200)
            ->assertJson(['data' => []]);
    }

    public function test_get_list_filters_by_search(): void
    {
        Book::factory()->create(['title' => 'Clean Code', 'author_id' => $this->author->id]);
        Book::factory()->create(['title' => 'The Pragmatic Programmer', 'author_id' => $this->author->id]);

        $response = $this->getJson('/api/books?search=Clean');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertSame('Clean Code', $data[0]['title']);
    }

    public function test_get_list_respects_per_page_param(): void
    {
        Book::factory()->count(10)->create(['author_id' => $this->author->id]);

        $response = $this->getJson('/api/books?perPage=3');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_get_list_sorts_by_title_desc(): void
    {
        Book::factory()->create(['title' => 'AAA Book', 'author_id' => $this->author->id]);
        Book::factory()->create(['title' => 'ZZZ Book', 'author_id' => $this->author->id]);

        $response = $this->getJson('/api/books?sortBy=title&sortDirection=desc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertSame('ZZZ Book', $data[0]['title']);
    }

    public function test_get_list_validates_sort_direction(): void
    {
        $response = $this->getJson('/api/books?sortDirection=invalid');

        $response->assertStatus(422);
    }

    public function test_get_list_validates_per_page_min(): void
    {
        $response = $this->getJson('/api/books?perPage=0');

        $response->assertStatus(422);
    }

    public function test_get_list_validates_per_page_max(): void
    {
        $response = $this->getJson('/api/books?perPage=101');

        $response->assertStatus(422);
    }

    // -----------------------------------------------------------------------
    // GET /api/books/{book}
    // -----------------------------------------------------------------------

    public function test_get_by_id_returns_book(): void
    {
        $book = Book::factory()->create([
            'title' => 'Refactoring',
            'author_id' => $this->author->id,
        ]);

        $response = $this->getJson("/api/books/{$book->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'title', 'slug', 'authorId', 'description', 'price', 'stock', 'status', 'createdAt', 'updatedAt'],
            ])
            ->assertJsonPath('data.id', $book->id)
            ->assertJsonPath('data.title', 'Refactoring');
    }

    public function test_get_by_id_returns_404_for_nonexistent_book(): void
    {
        $response = $this->getJson('/api/books/99999');

        $response->assertStatus(404);
    }

    // -----------------------------------------------------------------------
    // POST /api/books
    // -----------------------------------------------------------------------

    public function test_create_book_returns_201_with_data(): void
    {
        $response = $this->postJson('/api/books', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'title', 'slug', 'authorId', 'description', 'price', 'stock', 'status', 'createdAt', 'updatedAt'],
            ])
            ->assertJsonPath('data.title', 'Clean Code');
    }

    public function test_create_book_persists_to_database(): void
    {
        $this->postJson('/api/books', $this->validPayload(['title' => 'Design Patterns', 'slug' => 'design-patterns']));

        $this->assertDatabaseHas('books', ['title' => 'Design Patterns']);
    }

    public function test_create_book_requires_title(): void
    {
        $response = $this->postJson('/api/books', $this->validPayload(['title' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_create_book_requires_slug(): void
    {
        $response = $this->postJson('/api/books', $this->validPayload(['slug' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    }

    public function test_create_book_slug_must_be_valid_format(): void
    {
        $response = $this->postJson('/api/books', $this->validPayload(['slug' => 'Invalid Slug!']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    }

    public function test_create_book_requires_valid_author_id(): void
    {
        $response = $this->postJson('/api/books', $this->validPayload(['authorId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['authorId']);
    }

    public function test_create_book_requires_price(): void
    {
        $response = $this->postJson('/api/books', $this->validPayload(['price' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    public function test_create_book_price_cannot_be_negative(): void
    {
        $response = $this->postJson('/api/books', $this->validPayload(['price' => -1]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    public function test_create_book_status_must_be_valid(): void
    {
        $response = $this->postJson('/api/books', $this->validPayload(['status' => 'invalid_status']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_create_book_title_max_255_characters(): void
    {
        $response = $this->postJson('/api/books', $this->validPayload(['title' => str_repeat('A', 256)]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_create_book_accepts_nullable_publish_year(): void
    {
        $response = $this->postJson('/api/books', $this->validPayload(['publishYear' => null]));

        $response->assertStatus(201);
    }

    public function test_create_book_publish_year_min_1900(): void
    {
        $response = $this->postJson('/api/books', $this->validPayload(['publishYear' => 1800]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['publishYear']);
    }

    // -----------------------------------------------------------------------
    // PUT /api/books/{book}
    // -----------------------------------------------------------------------

    public function test_update_book_returns_200_with_updated_data(): void
    {
        $book = Book::factory()->create(['author_id' => $this->author->id]);

        $response = $this->putJson("/api/books/{$book->id}", $this->validPayload(['title' => 'Updated Title', 'slug' => 'updated-title']));

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Title');
    }

    public function test_update_book_persists_changes_to_database(): void
    {
        $book = Book::factory()->create(['author_id' => $this->author->id]);

        $this->putJson("/api/books/{$book->id}", $this->validPayload(['title' => 'Persisted Title', 'slug' => 'persisted-title']));

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Persisted Title',
        ]);
    }

    public function test_update_book_returns_404_for_nonexistent_book(): void
    {
        $response = $this->putJson('/api/books/99999', $this->validPayload());

        $response->assertStatus(404);
    }

    public function test_update_book_requires_title(): void
    {
        $book = Book::factory()->create(['author_id' => $this->author->id]);

        $response = $this->putJson("/api/books/{$book->id}", $this->validPayload(['title' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_update_book_title_max_255_characters(): void
    {
        $book = Book::factory()->create(['author_id' => $this->author->id]);

        $response = $this->putJson("/api/books/{$book->id}", $this->validPayload(['title' => str_repeat('B', 256)]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    // -----------------------------------------------------------------------
    // DELETE /api/books/{book}
    // -----------------------------------------------------------------------

    public function test_delete_book_returns_200_with_message(): void
    {
        $book = Book::factory()->create(['author_id' => $this->author->id]);

        $response = $this->deleteJson("/api/books/{$book->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Book deleted successfully']);
    }

    public function test_delete_book_removes_from_database(): void
    {
        $book = Book::factory()->create(['author_id' => $this->author->id]);

        $this->deleteJson("/api/books/{$book->id}");

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_delete_book_returns_404_for_nonexistent_book(): void
    {
        $response = $this->deleteJson('/api/books/99999');

        $response->assertStatus(404);
    }
}
