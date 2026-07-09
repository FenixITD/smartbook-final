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
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);
        $review = Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $mock = Mockery::mock(SearchSuggestReviewService::class);
        $mock->shouldReceive('execute')->withAnyArgs()->andReturn([
            ['id' => $review->id, 'user_name' => 'User', 'content' => 'Great!', 'url' => 'http://localhost/reviews/1']
        ]);
        $this->app->instance(SearchSuggestReviewService::class, $mock);

        $response = $this->actingAs($admin, 'sanctum')->getJson(route('api.reviews.suggest', ['q' => 'good']));

        $response->assertStatus(200)->assertJsonPath('0.id', $review->id);
    }
}
