<?php declare(strict_types=1);

namespace Tests\Feature\Api\Authors;

use App\Models\Author;
use App\Models\User;
use App\Services\Author\SearchSuggestAuthorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

final class AuthorApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_authors(): void
    {
        $response = $this->getJson('/api/authors');
        $response->assertStatus(401);
    }

    public function test_non_admin_cannot_create_author(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/authors', [
            'name' => 'Stephen King',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_get_authors_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Author::factory()->count(3)->create();

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/authors');

        $response->assertStatus(200)->assertJsonStructure(['data']);
    }

    public function test_admin_can_get_single_author(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = Author::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')->getJson("/api/authors/{$author->id}");

        $response->assertStatus(200)->assertJsonPath('data.id', $author->id);
    }

    public function test_admin_cannot_create_author_with_invalid_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/authors', [
            'name' => '',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
    }

    public function test_admin_can_create_author(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/authors', [
            'name' => 'J.R.R. Tolkien',
        ]);

        $response->assertStatus(201)->assertJsonPath('data.name', 'J.R.R. Tolkien');
    }

    public function test_admin_can_update_author(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = Author::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($admin, 'sanctum')->putJson("/api/authors/{$author->id}", [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200)->assertJsonPath('data.name', 'New Name');
    }

    public function test_admin_can_delete_author(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = Author::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/authors/{$author->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('authors', ['id' => $author->id]);
    }

    public function test_search_suggest_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = Author::factory()->create(['name' => 'Leo Tolstoy']);

        $mock = Mockery::mock(SearchSuggestAuthorService::class);
        $mock->shouldReceive('execute')->withAnyArgs()->andReturn([
            ['id' => $author->id, 'name' => 'Leo Tolstoy', 'url' => 'http://localhost/authors/1']
        ]);
        $this->app->instance(SearchSuggestAuthorService::class, $mock);

        $response = $this->actingAs($admin, 'sanctum')->getJson(route('api.authors.suggest', ['q' => 'leo']));

        $response->assertStatus(200)->assertJsonPath('0.name', 'Leo Tolstoy');
    }
}
