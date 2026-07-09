<?php declare(strict_types=1);

namespace Tests\Feature\Web\Genres;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GenreWebTest extends TestCase
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

    public function test_admin_can_view_genres_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Genre::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get('/genres');

        $response->assertStatus(200)->assertViewIs('genres.list');
    }

    public function test_admin_can_view_genre_create_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/genres/create');

        $response->assertStatus(200)->assertViewIs('genres.create');
    }

    public function test_admin_can_store_genre(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/genres', [
            'name' => 'Web Genre',
            'slug' => 'web-genre',
        ]);

        $response->assertRedirect(route('genres.index'))
            ->assertSessionHas('success', 'Genre created successfully.');

        $this->assertDatabaseHas('genres', ['name' => 'Web Genre']);
    }

    public function test_admin_can_update_genre(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $genre = Genre::factory()->create(['name' => 'Old Web Name', 'slug' => 'old-web-slug']);

        $response = $this->actingAs($admin)->put("/genres/{$genre->id}", [
            'name' => 'New Web Name',
            'slug' => 'new-web-slug',
        ]);

        $response->assertRedirect(route('genres.index'))
            ->assertSessionHas('success', 'Genre updated successfully.');

        $this->assertDatabaseHas('genres', ['id' => $genre->id, 'name' => 'New Web Name']);
    }

    public function test_admin_can_delete_genre(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $genre = Genre::factory()->create();

        $response = $this->actingAs($admin)->delete("/genres/{$genre->id}");

        $response->assertRedirect(route('genres.index'))
            ->assertSessionHas('success', 'Genre deleted successfully.');

        $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
    }
}
