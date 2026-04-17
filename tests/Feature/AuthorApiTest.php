<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class AuthorApiTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // GET /api/authors
    // -----------------------------------------------------------------------

    public function testGetListReturns200WithAuthors(): void
    {
        Author::factory()->count(3)->create();

        $response = $this->getJson('/api/authors');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'createdAt', 'updatedAt'],
                ],
            ]);
    }

    public function testGetListReturnsEmptyDataWhenNoAuthors(): void
    {
        $response = $this->getJson('/api/authors');

        $response->assertStatus(200)
            ->assertJson(['data' => []]);
    }

    public function testGetListFiltersBySearch(): void
    {
        Author::factory()->create(['name' => 'Leo Tolstoy']);
        Author::factory()->create(['name' => 'Fyodor Dostoevsky']);

        $response = $this->getJson('/api/authors?search=Tolstoy');

        $response->assertStatus(200);

        $data = $response->json('data');
        self::assertCount(1, $data);
        self::assertSame('Leo Tolstoy', $data[0]['name']);
    }

    public function testGetListRespectsPerPageParam(): void
    {
        Author::factory()->count(10)->create();

        $response = $this->getJson('/api/authors?perPage=3');

        $response->assertStatus(200);
        self::assertCount(3, $response->json('data'));
    }

    public function testGetListSortsByNameDesc(): void
    {
        Author::factory()->create(['name' => 'Anton Chekhov']);
        Author::factory()->create(['name' => 'Zelda Fitzgerald']);

        $response = $this->getJson('/api/authors?sortBy=name&sortDirection=desc');

        $response->assertStatus(200);
        $data = $response->json('data');
        self::assertSame('Zelda Fitzgerald', $data[0]['name']);
    }

    public function testGetListValidatesSortDirection(): void
    {
        $response = $this->getJson('/api/authors?sortDirection=invalid');

        $response->assertStatus(422);
    }

    public function testGetListValidatesPerPageMin(): void
    {
        $response = $this->getJson('/api/authors?perPage=0');

        $response->assertStatus(422);
    }

    public function testGetListValidatesPerPageMax(): void
    {
        $response = $this->getJson('/api/authors?perPage=101');

        $response->assertStatus(422);
    }

    // -----------------------------------------------------------------------
    // GET /api/authors/{author}
    // -----------------------------------------------------------------------

    public function testGetByIdReturnsAuthor(): void
    {
        $author = Author::factory()->create(['name' => 'Ivan Turgenev']);

        $response = $this->getJson("/api/authors/{$author->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'createdAt', 'updatedAt'],
            ])
            ->assertJsonPath('data.id', $author->id)
            ->assertJsonPath('data.name', 'Ivan Turgenev');
    }

    public function testGetByIdReturns404ForNonexistentAuthor(): void
    {
        $response = $this->getJson('/api/authors/99999');

        $response->assertStatus(404);
    }

    // -----------------------------------------------------------------------
    // POST /api/authors
    // -----------------------------------------------------------------------

    public function testCreateAuthorReturns201WithData(): void
    {
        $response = $this->postJson('/api/authors', [
            'name' => 'Nikolai Gogol',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'createdAt', 'updatedAt'],
            ])
            ->assertJsonPath('data.name', 'Nikolai Gogol');
    }

    public function testCreateAuthorPersistsToDatabase(): void
    {
        $this->postJson('/api/authors', ['name' => 'Alexander Pushkin']);

        $this->assertDatabaseHas('authors', ['name' => 'Alexander Pushkin']);
    }

    public function testCreateAuthorRequiresName(): void
    {
        $response = $this->postJson('/api/authors', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function testCreateAuthorNameMustBeString(): void
    {
        $response = $this->postJson('/api/authors', ['name' => 12345]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function testCreateAuthorNameMax255Characters(): void
    {
        $response = $this->postJson('/api/authors', [
            'name' => str_repeat('A', 256),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function testCreateAuthorAcceptsNameOf255Characters(): void
    {
        $response = $this->postJson('/api/authors', [
            'name' => str_repeat('A', 255),
        ]);

        $response->assertStatus(201);
    }

    // -----------------------------------------------------------------------
    // PUT /api/authors/{author}
    // -----------------------------------------------------------------------

    public function testUpdateAuthorReturns200WithUpdatedData(): void
    {
        $author = Author::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson("/api/authors/{$author->id}", [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'New Name');
    }

    public function testUpdateAuthorPersistsChangesToDatabase(): void
    {
        $author = Author::factory()->create(['name' => 'Old Name']);

        $this->putJson("/api/authors/{$author->id}", ['name' => 'Updated Name']);

        $this->assertDatabaseHas('authors', [
            'id' => $author->id,
            'name' => 'Updated Name',
        ]);
    }

    public function testUpdateAuthorReturns404ForNonexistentAuthor(): void
    {
        $response = $this->putJson('/api/authors/99999', ['name' => 'Some Name']);

        $response->assertStatus(404);
    }

    public function testUpdateAuthorRequiresName(): void
    {
        $author = Author::factory()->create();

        $response = $this->putJson("/api/authors/{$author->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function testUpdateAuthorNameMax255Characters(): void
    {
        $author = Author::factory()->create();

        $response = $this->putJson("/api/authors/{$author->id}", [
            'name' => str_repeat('B', 256),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    // -----------------------------------------------------------------------
    // DELETE /api/authors/{author}
    // -----------------------------------------------------------------------

    public function testDeleteAuthorReturns200WithMessage(): void
    {
        $author = Author::factory()->create();

        $response = $this->deleteJson("/api/authors/{$author->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Author deleted successfully']);
    }

    public function testDeleteAuthorRemovesFromDatabase(): void
    {
        $author = Author::factory()->create();

        $this->deleteJson("/api/authors/{$author->id}");

        $this->assertDatabaseMissing('authors', ['id' => $author->id]);
    }

    public function testDeleteAuthorReturns404ForNonexistentAuthor(): void
    {
        $response = $this->deleteJson('/api/authors/99999');

        $response->assertStatus(404);
    }
}
