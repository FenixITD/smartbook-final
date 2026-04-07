<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorApiTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // GET /api/authors
    // -----------------------------------------------------------------------

    public function test_get_list_returns_200_with_authors(): void
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

    public function test_get_list_returns_empty_data_when_no_authors(): void
    {
        $response = $this->getJson('/api/authors');

        $response->assertStatus(200)
            ->assertJson(['data' => []]);
    }

    public function test_get_list_filters_by_search(): void
    {
        Author::factory()->create(['name' => 'Leo Tolstoy']);
        Author::factory()->create(['name' => 'Fyodor Dostoevsky']);

        $response = $this->getJson('/api/authors?search=Tolstoy');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertSame('Leo Tolstoy', $data[0]['name']);
    }

    public function test_get_list_respects_per_page_param(): void
    {
        Author::factory()->count(10)->create();

        $response = $this->getJson('/api/authors?perPage=3');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_get_list_sorts_by_name_desc(): void
    {
        Author::factory()->create(['name' => 'Anton Chekhov']);
        Author::factory()->create(['name' => 'Zelda Fitzgerald']);

        $response = $this->getJson('/api/authors?sortBy=name&sortDirection=desc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertSame('Zelda Fitzgerald', $data[0]['name']);
    }

    public function test_get_list_validates_sort_direction(): void
    {
        $response = $this->getJson('/api/authors?sortDirection=invalid');

        $response->assertStatus(422);
    }

    public function test_get_list_validates_per_page_min(): void
    {
        $response = $this->getJson('/api/authors?perPage=0');

        $response->assertStatus(422);
    }

    public function test_get_list_validates_per_page_max(): void
    {
        $response = $this->getJson('/api/authors?perPage=101');

        $response->assertStatus(422);
    }

    // -----------------------------------------------------------------------
    // GET /api/authors/{author}
    // -----------------------------------------------------------------------

    public function test_get_by_id_returns_author(): void
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

    public function test_get_by_id_returns_404_for_nonexistent_author(): void
    {
        $response = $this->getJson('/api/authors/99999');

        $response->assertStatus(404);
    }

    // -----------------------------------------------------------------------
    // POST /api/authors
    // -----------------------------------------------------------------------

    public function test_create_author_returns_201_with_data(): void
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

    public function test_create_author_persists_to_database(): void
    {
        $this->postJson('/api/authors', ['name' => 'Alexander Pushkin']);

        $this->assertDatabaseHas('authors', ['name' => 'Alexander Pushkin']);
    }

    public function test_create_author_requires_name(): void
    {
        $response = $this->postJson('/api/authors', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_create_author_name_must_be_string(): void
    {
        $response = $this->postJson('/api/authors', ['name' => 12345]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_create_author_name_max_255_characters(): void
    {
        $response = $this->postJson('/api/authors', [
            'name' => str_repeat('A', 256),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_create_author_accepts_name_of_255_characters(): void
    {
        $response = $this->postJson('/api/authors', [
            'name' => str_repeat('A', 255),
        ]);

        $response->assertStatus(201);
    }

    // -----------------------------------------------------------------------
    // PUT /api/authors/{author}
    // -----------------------------------------------------------------------

    public function test_update_author_returns_200_with_updated_data(): void
    {
        $author = Author::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson("/api/authors/{$author->id}", [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'New Name');
    }

    public function test_update_author_persists_changes_to_database(): void
    {
        $author = Author::factory()->create(['name' => 'Old Name']);

        $this->putJson("/api/authors/{$author->id}", ['name' => 'Updated Name']);

        $this->assertDatabaseHas('authors', [
            'id' => $author->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_update_author_returns_404_for_nonexistent_author(): void
    {
        $response = $this->putJson('/api/authors/99999', ['name' => 'Some Name']);

        $response->assertStatus(404);
    }

    public function test_update_author_requires_name(): void
    {
        $author = Author::factory()->create();

        $response = $this->putJson("/api/authors/{$author->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_author_name_max_255_characters(): void
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

    public function test_delete_author_returns_200_with_message(): void
    {
        $author = Author::factory()->create();

        $response = $this->deleteJson("/api/authors/{$author->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Author deleted successfully']);
    }

    public function test_delete_author_removes_from_database(): void
    {
        $author = Author::factory()->create();

        $this->deleteJson("/api/authors/{$author->id}");

        $this->assertDatabaseMissing('authors', ['id' => $author->id]);
    }

    public function test_delete_author_returns_404_for_nonexistent_author(): void
    {
        $response = $this->deleteJson('/api/authors/99999');

        $response->assertStatus(404);
    }
}
