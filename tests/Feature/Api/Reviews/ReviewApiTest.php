<?php declare(strict_types=1);

namespace Tests\Feature\Api\Reviews;

use App\Models\Author;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use App\Services\Review\SearchSuggestReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

final class ReviewApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_get_reviews_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $books = Book::factory()->count(3)->create(['author_id' => $author->id]);

        foreach ($books as $b) {
            Review::factory()->create(['user_id' => $user->id, 'book_id' => $b->id]);
        }

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/reviews');

        $response->assertStatus(200)->assertJsonStructure(['data']);
    }

    public function test_search_suggest_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $mock = Mockery::mock(SearchSuggestReviewService::class);
        $mock->shouldReceive('execute')
            ->once()
            ->with('great')
            ->andReturn([
                ['id' => 1, 'user_name' => 'Alice', 'content' => 'Great book!', 'url' => 'http://localhost/reviews/1'],
                ['id' => 2, 'user_name' => 'Bob', 'content' => 'Really great read', 'url' => 'http://localhost/reviews/2'],
            ]);
        $this->app->instance(SearchSuggestReviewService::class, $mock);

        $response = $this->actingAs($admin, 'sanctum')->getJson(route('api.reviews.suggest', ['q' => 'great']));

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonStructure([
                '*' => ['id', 'user_name', 'content', 'url'],
            ]);
    }

    public function test_search_suggest_returns_empty_for_no_match(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $mock = Mockery::mock(SearchSuggestReviewService::class);
        $mock->shouldReceive('execute')
            ->once()
            ->with('zzz_nonexistent')
            ->andReturn([]);
        $this->app->instance(SearchSuggestReviewService::class, $mock);

        $response = $this->actingAs($admin, 'sanctum')->getJson(route('api.reviews.suggest', ['q' => 'zzz_nonexistent']));

        $response->assertStatus(200)->assertJson([]);
    }

    public function test_search_suggest_rejects_short_query(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')->getJson(route('api.reviews.suggest', ['q' => 'a']));

        $response->assertStatus(422)->assertJsonValidationErrors(['q']);
    }

    public function test_search_suggest_requires_auth(): void
    {
        $response = $this->getJson(route('api.reviews.suggest', ['q' => 'test']));

        $response->assertStatus(401);
    }

    public function test_non_admin_cannot_access_suggest(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user, 'sanctum')->getJson(route('api.reviews.suggest', ['q' => 'test']));

        $response->assertStatus(403);
    }
}
