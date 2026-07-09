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
        $genre = Genre::factory()->create(['name' => 'Fantasy']);

        $mock = Mockery::mock(SearchSuggestGenreService::class);
        $mock->shouldReceive('execute')->withAnyArgs()->andReturn([
            ['id' => $genre->id, 'name' => 'Fantasy', 'url' => 'http://localhost/genres/1']
        ]);
        $this->app->instance(SearchSuggestGenreService::class, $mock);

        $response = $this->actingAs($admin, 'sanctum')->getJson(route('api.genres.suggest', ['q' => 'fan']));

        $response->assertStatus(200)->assertJsonPath('0.name', 'Fantasy');
    }
}
