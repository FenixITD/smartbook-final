<?php declare(strict_types=1);

namespace Tests\Feature\Web\Reviews;

use App\Http\Controllers\Web\Reviews\DeletePublicReviewController;
use App\Http\Controllers\Web\Reviews\StorePublicReviewController;
use App\Http\Controllers\Web\Reviews\UpdatePublicReviewController;
use App\Models\Author;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class ReviewWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (class_exists(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)) {
            $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        }

        // Register isolated test routes to hit the controllers directly
        Route::post('/_test/public-reviews', StorePublicReviewController::class);
        Route::put('/_test/public-reviews/{review}', UpdatePublicReviewController::class);
        Route::delete('/_test/public-reviews/{review}', DeletePublicReviewController::class);
    }

    public function test_admin_can_view_reviews_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // This route works naturally from your app's web routes
        $response = $this->actingAs($admin)->get('/reviews');

        $response->assertStatus(200)->assertViewIs('reviews.list');
    }

    public function test_public_user_can_store_review(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        $response = $this->actingAs($user)
            ->from('/catalog/book')
            ->post('/_test/public-reviews', [
                'book_id' => $book->id,
                'rating' => 4,
                'comment' => 'Public review comment',
            ]);

        $response->assertRedirect('/catalog/book')->assertSessionHas('success');
        $this->assertDatabaseHas('reviews', ['user_id' => $user->id, 'book_id' => $book->id, 'rating' => 4]);
    }

    public function test_public_user_cannot_store_duplicate_review(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $response = $this->actingAs($user)
            ->from('/catalog/book')
            ->post('/_test/public-reviews', [
                'book_id' => $book->id,
                'rating' => 5,
                'comment' => 'Another review',
            ]);

        $response->assertRedirect('/catalog/book')->assertSessionHasErrors(['book_id']);
    }

    public function test_public_user_can_update_own_review(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);
        $review = Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'rating' => 3]);

        $response = $this->actingAs($user)
            ->from('/catalog/book')
            ->put("/_test/public-reviews/{$review->id}", [
                'book_id' => $book->id,
                'rating' => 5,
                'comment' => 'Updated my own review',
            ]);

        $response->assertRedirect('/catalog/book')->assertSessionHas('success');
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'rating' => 5]);
    }

    public function test_public_user_cannot_update_others_review(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);
        $review = Review::factory()->create(['user_id' => $otherUser->id, 'book_id' => $book->id]);

        $response = $this->actingAs($user)
            ->from('/catalog/book')
            ->put("/_test/public-reviews/{$review->id}", [
                'book_id' => $book->id,
                'rating' => 5,
            ]);

        $response->assertStatus(403);
    }

    public function test_public_user_can_delete_own_review(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);
        $review = Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $response = $this->actingAs($user)
            ->from('/catalog/book')
            ->delete("/_test/public-reviews/{$review->id}");

        $response->assertRedirect('/catalog/book')->assertSessionHas('success');
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }
}
