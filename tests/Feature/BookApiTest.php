<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class BookApiTest extends TestCase
{
    use RefreshDatabase;

    private Author $author;

    // -----------------------------------------------------------------------
    // GET /api/books
    // -----------------------------------------------------------------------

    public function testGetListReturns200WithBooks(): void
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

    public function testGetListReturnsEmptyDataWhenNoBooks(): void
    {
        $response = $this->getJson('/api/books');

        $response->assertStatus(200)
            ->assertJson(['data' => []]);
    }

    public function testGetListFiltersBySearch(): void
    {
        Book::factory()->create(['title' => 'Clean Code', 'author_id' => $this->author->id]);
        Book::factory()->create(['title' => 'The Pragmatic Programmer', 'author_id' => $this->author->id]);

        $response = $this->getJson('/api/books?search=Clean');

        $response->assertStatus(200);

        $data = $response->json('data');
        self::assertCount(1, $data);
        self::assertSame('Clean Code', $data[0]['title']);
    }

    public function testGetListRespectsPerPageParam(): void
    {
        Book::factory()->count(10)->create(['author_id' => $this->author->id]);

        $response = $this->getJson('/api/books?perPage=3');

        $response->assertStatus(200);
        self::assertCount(3, $response->json('data'));
    }

    public function testGetListSortsByTitleDesc(): void
    {
        Book::factory()->create(['title' => 'AAA Book', 'author_id' => $this->author->id]);
        Book::factory()->create(['title' => 'ZZZ Book', 'author_id' => $this->author->id]);

        $response = $this->getJson('/api/books?sortBy=title&sortDirection=desc');

        $response->assertStatus(200);
        $data = $response->json('data');
        self::assertSame('ZZZ Book', $data[0]['title']);
    }

    public function testGetListValidatesSortDirection(): void
    {
        $response = $this->getJson('/api/books?sortDirection=invalid');

        $response->assertStatus(422);
    }

    public function testGetListValidatesPerPageMin(): void
    {
        $response = $this->getJson('/api/books?perPage=0');

        $response->assertStatus(422);
    }

    public function testGetListValidatesPerPageMax(): void
    {
        $response = $this->getJson('/api/books?perPage=101');

        $response->assertStatus(422);
    }

    // -----------------------------------------------------------------------
    // GET /api/books/{book}
    // -----------------------------------------------------------------------

    public function testGetByIdReturnsBook(): void
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

    public function testGetByIdReturns404ForNonexistentBook(): void
    {
        $response = $this->getJson('/api/books/99999');

        $response->assertStatus(404);
    }

    // -----------------------------------------------------------------------
    // POST /api/books
    // -----------------------------------------------------------------------

    public function testCreateBookReturns201WithData(): void
    {
        $response = $this->postJson('/api/books', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'title', 'slug', 'authorId', 'description', 'price', 'stock', 'status', 'createdAt', 'updatedAt'],
            ])
            ->assertJsonPath('data.title', 'Clean Code');
    }

    public function testCreateBookPersistsToDatabase(): void
    {
        $this->postJson('/api/books', $this->validPayload(['title' => 'Design Patterns', 'slug' => 'design-patterns']));

        $this->assertDatabaseHas('books', ['title' => 'Design Patterns']);
    }

    public function testCreateBookRequiresTitle(): void
    {
        $response = $this->postJson('/api/books', $this->validPayload(['title' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function testCreateBookRequiresSlug(): void
    {
        $response = $this->postJson('/api/books', $this->validPayload(['slug' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    }

    public function testCreateBookSlugMustBeValidFormat(): void
    {
        $response = $this->postJson('/api/books', $this->validPayload(['slug' => 'Invalid Slug!']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    }

    public function testCreateBookRequiresValidAuthorId(): void
    {
        $response = $this->postJson('/api/books', $this->validPayload(['authorId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['authorId']);
    }

    public function testCreateBookRequiresPrice(): void
    {
        $response = $this->postJson('/api/books', $this->validPayload(['price' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    public function testCreateBookPriceCannotBeNegative(): void
    {
        $response = $this->postJson('/api/books', $this->validPayload(['price' => -1]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    public function testCreateBookStatusMustBeValid(): void
    {
        $response = $this->postJson('/api/books', $this->validPayload(['status' => 'invalid_status']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function testCreateBookTitleMax255Characters(): void
    {
        $response = $this->postJson('/api/books', $this->validPayload(['title' => str_repeat('A', 256)]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function testCreateBookAcceptsNullablePublishYear(): void
    {
        $response = $this->postJson('/api/books', $this->validPayload(['publishYear' => null]));

        $response->assertStatus(201);
    }

    public function testCreateBookPublishYearMin1900(): void
    {
        $response = $this->postJson('/api/books', $this->validPayload(['publishYear' => 1800]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['publishYear']);
    }

    // -----------------------------------------------------------------------
    // PUT /api/books/{book}
    // -----------------------------------------------------------------------

    public function testUpdateBookReturns200WithUpdatedData(): void
    {
        $book = Book::factory()->create(['author_id' => $this->author->id]);

        $response = $this->putJson("/api/books/{$book->id}", $this->validPayload(['title' => 'Updated Title', 'slug' => 'updated-title']));

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Title');
    }

    public function testUpdateBookPersistsChangesToDatabase(): void
    {
        $book = Book::factory()->create(['author_id' => $this->author->id]);

        $this->putJson("/api/books/{$book->id}", $this->validPayload(['title' => 'Persisted Title', 'slug' => 'persisted-title']));

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Persisted Title',
        ]);
    }

    public function testUpdateBookReturns404ForNonexistentBook(): void
    {
        $response = $this->putJson('/api/books/99999', $this->validPayload());

        $response->assertStatus(404);
    }

    public function testUpdateBookRequiresTitle(): void
    {
        $book = Book::factory()->create(['author_id' => $this->author->id]);

        $response = $this->putJson("/api/books/{$book->id}", $this->validPayload(['title' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function testUpdateBookTitleMax255Characters(): void
    {
        $book = Book::factory()->create(['author_id' => $this->author->id]);

        $response = $this->putJson("/api/books/{$book->id}", $this->validPayload(['title' => str_repeat('B', 256)]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    // -----------------------------------------------------------------------
    // DELETE /api/books/{book}
    // -----------------------------------------------------------------------

    public function testDeleteBookReturns200WithMessage(): void
    {
        $book = Book::factory()->create(['author_id' => $this->author->id]);

        $response = $this->deleteJson("/api/books/{$book->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Book deleted successfully']);
    }

    public function testDeleteBookRemovesFromDatabase(): void
    {
        $book = Book::factory()->create(['author_id' => $this->author->id]);

        $this->deleteJson("/api/books/{$book->id}");

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function testDeleteBookReturns404ForNonexistentBook(): void
    {
        $response = $this->deleteJson('/api/books/99999');

        $response->assertStatus(404);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->author = Author::factory()->create();
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
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
}
