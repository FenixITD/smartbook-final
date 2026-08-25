<?php declare(strict_types=1);

namespace Tests\Feature\Api\Genres;

use App\Models\Genre;
use App\Models\User;
use App\Services\Genre\SearchSuggestGenreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

final class GenreApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_genres(): void
    {
        $response = $this->getJson('/api/genres');
        $response->assertStatus(401);
    }

    public function test_admin_can_get_genres_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Genre::factory()->count(3)->create();

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/genres');

        $response->assertStatus(200)->assertJsonStructure(['data']);
    }

    public function test_search_suggest_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $mock = Mockery::mock(SearchSuggestGenreService::class);
        $mock->shouldReceive('execute')
            ->once()
            ->with('fan')
            ->andReturn([
                ['id' => 1, 'name' => 'Fantasy', 'url' => 'http://localhost/genres/1'],
                ['id' => 2, 'name' => 'Fairy Tale', 'url' => 'http://localhost/genres/2'],
            ]);
        $this->app->instance(SearchSuggestGenreService::class, $mock);

        $response = $this->actingAs($admin, 'sanctum')->getJson(route('api.genres.suggest', ['q' => 'fan']));

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonStructure([
                '*' => ['id', 'name', 'url'],
            ]);
    }

    public function test_search_suggest_returns_empty_for_no_match(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $mock = Mockery::mock(SearchSuggestGenreService::class);
        $mock->shouldReceive('execute')
            ->once()
            ->with('zzz_nonexistent')
            ->andReturn([]);
        $this->app->instance(SearchSuggestGenreService::class, $mock);

        $response = $this->actingAs($admin, 'sanctum')->getJson(route('api.genres.suggest', ['q' => 'zzz_nonexistent']));

        $response->assertStatus(200)->assertJson([]);
    }

    public function test_search_suggest_rejects_short_query(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')->getJson(route('api.genres.suggest', ['q' => 'a']));

        $response->assertStatus(422)->assertJsonValidationErrors(['q']);
    }

    public function test_search_suggest_requires_auth(): void
    {
        $response = $this->getJson(route('api.genres.suggest', ['q' => 'test']));

        $response->assertStatus(401);
    }

    public function test_non_admin_cannot_access_suggest(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user, 'sanctum')->getJson(route('api.genres.suggest', ['q' => 'test']));

        $response->assertStatus(403);
    }
}
