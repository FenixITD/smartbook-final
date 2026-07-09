<?php declare(strict_types=1);

namespace Tests\Feature\Web\Authors;

use App\Models\Author;
use App\Models\User;
use App\Services\Author\SearchAuthorByQueryService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

final class AuthorWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (class_exists(ValidateCsrfToken::class)) {
            $this->withoutMiddleware(ValidateCsrfToken::class);
        }
    }

    public function test_admin_can_view_authors_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Author::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get('/authors');

        $response->assertStatus(200)->assertViewIs('authors.list');
    }

    public function test_admin_can_search_web_authors_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = Author::factory()->create(['name' => 'Web Searchable Author']);

        $mock = Mockery::mock(SearchAuthorByQueryService::class);
        $mock->shouldReceive('searchPaginated')->andReturn([[$author->id], 1]);
        $this->app->instance(SearchAuthorByQueryService::class, $mock);

        $response = $this->actingAs($admin)->get('/authors?search=Web');

        $response->assertStatus(200)->assertViewIs('authors.list');
    }

    public function test_admin_can_view_author_create_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/authors/create');

        $response->assertStatus(200)->assertViewIs('authors.create');
    }

    public function test_admin_can_store_author(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/authors', [
            'name' => 'Web Author',
        ]);

        $response->assertRedirect(route('authors.index'))
            ->assertSessionHas('success', 'Author created successfully.');

        $this->assertDatabaseHas('authors', ['name' => 'Web Author']);
    }

    public function test_admin_can_view_single_author(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = Author::factory()->create();

        $response = $this->actingAs($admin)->get("/authors/{$author->id}");

        $response->assertStatus(200)->assertViewIs('authors.show');
    }

    public function test_admin_can_view_author_edit_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = Author::factory()->create();

        $response = $this->actingAs($admin)->get("/authors/{$author->id}/edit");

        $response->assertStatus(200)->assertViewIs('authors.edit');
    }

    public function test_admin_can_update_author(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = Author::factory()->create(['name' => 'Old Web Name']);

        $response = $this->actingAs($admin)->put("/authors/{$author->id}", [
            'name' => 'New Web Name',
        ]);

        $response->assertRedirect(route('authors.index'))
            ->assertSessionHas('success', 'Author updated successfully.');

        $this->assertDatabaseHas('authors', ['id' => $author->id, 'name' => 'New Web Name']);
    }

    public function test_admin_can_delete_author(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = Author::factory()->create();

        $response = $this->actingAs($admin)->delete("/authors/{$author->id}");

        $response->assertRedirect(route('authors.index'))
            ->assertSessionHas('success', 'Author deleted successfully.');

        $this->assertDatabaseMissing('authors', ['id' => $author->id]);
    }
}
