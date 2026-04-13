<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreApiTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Science Fiction',
            'slug' => 'science-fiction',
            'description' => 'A genre about science and the future.',
        ], $overrides);
    }

    // -----------------------------------------------------------------------
    // GET /api/genres
    // -----------------------------------------------------------------------

    public function test_get_list_returns_200_with_genres(): void
    {
        Genre::factory()->count(3)->create();

        $response = $this->getJson('/api/genres');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'description', 'createdAt', 'updatedAt'],
                ],
            ]);
    }

    public function test_get_list_returns_empty_data_when_no_genres(): void
    {
        $response = $this->getJson('/api/genres');

        $response->assertStatus(200)
            ->assertJson(['data' => []]);
    }

    public function test_get_list_filters_by_search(): void
    {
        Genre::factory()->create(['name' => 'Science Fiction']);
        Genre::factory()->create(['name' => 'Fantasy']);

        $response = $this->getJson('/api/genres?search=Science');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertSame('Science Fiction', $data[0]['name']);
    }

    public function test_get_list_respects_per_page_param(): void
    {
        Genre::factory()->count(10)->create();

        $response = $this->getJson('/api/genres?perPage=3');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_get_list_sorts_by_name_desc(): void
    {
        Genre::factory()->create(['name' => 'AAA Genre']);
        Genre::factory()->create(['name' => 'ZZZ Genre']);

        $response = $this->getJson('/api/genres?sortBy=name&sortDirection=desc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertSame('ZZZ Genre', $data[0]['name']);
    }

    public function test_get_list_validates_sort_direction(): void
    {
        $response = $this->getJson('/api/genres?sortDirection=invalid');

        $response->assertStatus(422);
    }

    public function test_get_list_validates_per_page_min(): void
    {
        $response = $this->getJson('/api/genres?perPage=0');

        $response->assertStatus(422);
    }

    public function test_get_list_validates_per_page_max(): void
    {
        $response = $this->getJson('/api/genres?perPage=101');

        $response->assertStatus(422);
    }

    // -----------------------------------------------------------------------
    // GET /api/genres/{genre}
    // -----------------------------------------------------------------------

    public function test_get_by_id_returns_genre(): void
    {
        $genre = Genre::factory()->create(['name' => 'Horror']);

        $response = $this->getJson("/api/genres/{$genre->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'slug', 'description', 'createdAt', 'updatedAt'],
            ])
            ->assertJsonPath('data.id', $genre->id)
            ->assertJsonPath('data.name', 'Horror');
    }

    public function test_get_by_id_returns_404_for_nonexistent_genre(): void
    {
        $response = $this->getJson('/api/genres/99999');

        $response->assertStatus(404);
    }

    // -----------------------------------------------------------------------
    // POST /api/genres
    // -----------------------------------------------------------------------

    public function test_create_genre_returns_201_with_data(): void
    {
        $response = $this->postJson('/api/genres', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'slug', 'description', 'createdAt', 'updatedAt'],
            ])
            ->assertJsonPath('data.name', 'Science Fiction');
    }

    public function test_create_genre_persists_to_database(): void
    {
        $this->postJson('/api/genres', $this->validPayload(['name' => 'Mystery', 'slug' => 'mystery']));

        $this->assertDatabaseHas('genres', ['name' => 'Mystery']);
    }

    public function test_create_genre_requires_name(): void
    {
        $response = $this->postJson('/api/genres', $this->validPayload(['name' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_create_genre_requires_slug(): void
    {
        $response = $this->postJson('/api/genres', $this->validPayload(['slug' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    }

    public function test_create_genre_slug_must_be_valid_format(): void
    {
        $response = $this->postJson('/api/genres', $this->validPayload(['slug' => 'Invalid Slug!']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    }

    public function test_create_genre_requires_description(): void
    {
        $response = $this->postJson('/api/genres', $this->validPayload(['description' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    }

    public function test_create_genre_name_max_255_characters(): void
    {
        $response = $this->postJson('/api/genres', $this->validPayload(['name' => str_repeat('A', 256)]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    // -----------------------------------------------------------------------
    // PUT /api/genres/{genre}
    // -----------------------------------------------------------------------

    public function test_update_genre_returns_200_with_updated_data(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->putJson("/api/genres/{$genre->id}", $this->validPayload([
            'name' => 'Updated Genre',
            'slug' => 'updated-genre',
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Genre');
    }

    public function test_update_genre_persists_changes_to_database(): void
    {
        $genre = Genre::factory()->create();

        $this->putJson("/api/genres/{$genre->id}", $this->validPayload([
            'name' => 'Persisted Genre',
            'slug' => 'persisted-genre',
        ]));

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => 'Persisted Genre',
        ]);
    }

    public function test_update_genre_returns_404_for_nonexistent_genre(): void
    {
        $response = $this->putJson('/api/genres/99999', $this->validPayload());

        $response->assertStatus(404);
    }

    public function test_update_genre_requires_name(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->putJson("/api/genres/{$genre->id}", $this->validPayload(['name' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_genre_name_max_255_characters(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->putJson("/api/genres/{$genre->id}", $this->validPayload(['name' => str_repeat('B', 256)]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    // -----------------------------------------------------------------------
    // DELETE /api/genres/{genre}
    // -----------------------------------------------------------------------

    public function test_delete_genre_returns_200_with_message(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->deleteJson("/api/genres/{$genre->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Genre deleted successfully']);
    }

    public function test_delete_genre_removes_from_database(): void
    {
        $genre = Genre::factory()->create();

        $this->deleteJson("/api/genres/{$genre->id}");

        $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
    }

    public function test_delete_genre_returns_404_for_nonexistent_genre(): void
    {
        $response = $this->deleteJson('/api/genres/99999');

        $response->assertStatus(404);
    }
}
