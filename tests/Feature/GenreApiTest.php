<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class GenreApiTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // GET /api/genres
    // -----------------------------------------------------------------------

    public function testGetListReturns200WithGenres(): void
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

    public function testGetListReturnsEmptyDataWhenNoGenres(): void
    {
        $response = $this->getJson('/api/genres');

        $response->assertStatus(200)
            ->assertJson(['data' => []]);
    }

    public function testGetListFiltersBySearch(): void
    {
        Genre::factory()->create(['name' => 'Science Fiction']);
        Genre::factory()->create(['name' => 'Fantasy']);

        $response = $this->getJson('/api/genres?search=Science');

        $response->assertStatus(200);

        $data = $response->json('data');
        self::assertCount(1, $data);
        self::assertSame('Science Fiction', $data[0]['name']);
    }

    public function testGetListRespectsPerPageParam(): void
    {
        Genre::factory()->count(10)->create();

        $response = $this->getJson('/api/genres?perPage=3');

        $response->assertStatus(200);
        self::assertCount(3, $response->json('data'));
    }

    public function testGetListSortsByNameDesc(): void
    {
        Genre::factory()->create(['name' => 'AAA Genre']);
        Genre::factory()->create(['name' => 'ZZZ Genre']);

        $response = $this->getJson('/api/genres?sortBy=name&sortDirection=desc');

        $response->assertStatus(200);
        $data = $response->json('data');
        self::assertSame('ZZZ Genre', $data[0]['name']);
    }

    public function testGetListValidatesSortDirection(): void
    {
        $response = $this->getJson('/api/genres?sortDirection=invalid');

        $response->assertStatus(422);
    }

    public function testGetListValidatesPerPageMin(): void
    {
        $response = $this->getJson('/api/genres?perPage=0');

        $response->assertStatus(422);
    }

    public function testGetListValidatesPerPageMax(): void
    {
        $response = $this->getJson('/api/genres?perPage=101');

        $response->assertStatus(422);
    }

    // -----------------------------------------------------------------------
    // GET /api/genres/{genre}
    // -----------------------------------------------------------------------

    public function testGetByIdReturnsGenre(): void
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

    public function testGetByIdReturns404ForNonexistentGenre(): void
    {
        $response = $this->getJson('/api/genres/99999');

        $response->assertStatus(404);
    }

    // -----------------------------------------------------------------------
    // POST /api/genres
    // -----------------------------------------------------------------------

    public function testCreateGenreReturns201WithData(): void
    {
        $response = $this->postJson('/api/genres', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'slug', 'description', 'createdAt', 'updatedAt'],
            ])
            ->assertJsonPath('data.name', 'Science Fiction');
    }

    public function testCreateGenrePersistsToDatabase(): void
    {
        $this->postJson('/api/genres', $this->validPayload(['name' => 'Mystery', 'slug' => 'mystery']));

        $this->assertDatabaseHas('genres', ['name' => 'Mystery']);
    }

    public function testCreateGenreRequiresName(): void
    {
        $response = $this->postJson('/api/genres', $this->validPayload(['name' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function testCreateGenreRequiresSlug(): void
    {
        $response = $this->postJson('/api/genres', $this->validPayload(['slug' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    }

    public function testCreateGenreSlugMustBeValidFormat(): void
    {
        $response = $this->postJson('/api/genres', $this->validPayload(['slug' => 'Invalid Slug!']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    }

    public function testCreateGenreRequiresDescription(): void
    {
        $response = $this->postJson('/api/genres', $this->validPayload(['description' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    }

    public function testCreateGenreNameMax255Characters(): void
    {
        $response = $this->postJson('/api/genres', $this->validPayload(['name' => str_repeat('A', 256)]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    // -----------------------------------------------------------------------
    // PUT /api/genres/{genre}
    // -----------------------------------------------------------------------

    public function testUpdateGenreReturns200WithUpdatedData(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->putJson("/api/genres/{$genre->id}", $this->validPayload([
            'name' => 'Updated Genre',
            'slug' => 'updated-genre',
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Genre');
    }

    public function testUpdateGenrePersistsChangesToDatabase(): void
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

    public function testUpdateGenreReturns404ForNonexistentGenre(): void
    {
        $response = $this->putJson('/api/genres/99999', $this->validPayload());

        $response->assertStatus(404);
    }

    public function testUpdateGenreRequiresName(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->putJson("/api/genres/{$genre->id}", $this->validPayload(['name' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function testUpdateGenreNameMax255Characters(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->putJson("/api/genres/{$genre->id}", $this->validPayload(['name' => str_repeat('B', 256)]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    // -----------------------------------------------------------------------
    // DELETE /api/genres/{genre}
    // -----------------------------------------------------------------------

    public function testDeleteGenreReturns200WithMessage(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->deleteJson("/api/genres/{$genre->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Genre deleted successfully']);
    }

    public function testDeleteGenreRemovesFromDatabase(): void
    {
        $genre = Genre::factory()->create();

        $this->deleteJson("/api/genres/{$genre->id}");

        $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
    }

    public function testDeleteGenreReturns404ForNonexistentGenre(): void
    {
        $response = $this->deleteJson('/api/genres/99999');

        $response->assertStatus(404);
    }

    /** @param array<string, mixed> $overrides
     *  @return array<string, mixed> */
    /** @param array<string, mixed> $overrides */
    /** @return array<string, mixed> */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Science Fiction',
            'slug' => 'science-fiction',
            'description' => 'A genre about science and the future.',
        ], $overrides);
    }
}
