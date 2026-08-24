<?php

declare(strict_types=1);

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

    public function test_public_user_cannot_retarget_review_to_another_book(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book1 = Book::factory()->create(['author_id' => $author->id]);
        $book2 = Book::factory()->create(['author_id' => $author->id]);
        $review = Review::factory()->create(['user_id' => $user->id, 'book_id' => $book1->id, 'rating' => 3]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book2->id, 'rating' => 4]);

        $response = $this->actingAs($user)
            ->from('/catalog/book')
            ->put("/_test/public-reviews/{$review->id}", [
                'book_id' => $book2->id,
                'rating' => 5,
                'comment' => 'Trying to retarget',
            ]);

        $response->assertRedirect('/catalog/book')->assertSessionHas('success');
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'book_id' => $book1->id, 'rating' => 5]);
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

    public function test_deleting_last_review_resets_book_rating(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);
        $review = Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'rating' => 5]);

        $book->refresh();
        $this->assertSame(1, (int) $book->ratings_count);

        $response = $this->actingAs($user)
            ->from('/catalog/book')
            ->delete("/_test/public-reviews/{$review->id}");

        $response->assertRedirect('/catalog/book')->assertSessionHas('success');
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);

        $book->refresh();
        $this->assertSame(0, (int) $book->ratings_count);
        $this->assertEqualsWithDelta(0.0, (float) $book->average_rating, 0.001);
    }

    public function test_admin_can_move_review_to_another_book_and_both_ratings_recalculated(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $firstBook = Book::factory()->create(['author_id' => $author->id]);
        $secondBook = Book::factory()->create(['author_id' => $author->id]);
        $review = Review::factory()->create(['user_id' => $user->id, 'book_id' => $firstBook->id, 'rating' => 5]);

        $response = $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->put("/reviews/{$review->id}", [
                'userId' => $user->id,
                'bookId' => $secondBook->id,
                'rating' => 5,
                'comment' => 'Moved to another book',
            ]);

        $response->assertRedirect(route('reviews.index'));
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'book_id' => $secondBook->id, 'rating' => 5]);

        $firstBook->refresh();
        $this->assertSame(0, (int) $firstBook->ratings_count);
        $this->assertEqualsWithDelta(0.0, (float) $firstBook->average_rating, 0.001);

        $secondBook->refresh();
        $this->assertSame(1, (int) $secondBook->ratings_count);
        $this->assertEqualsWithDelta(5.0, (float) $secondBook->average_rating, 0.001);
    }

    public function test_moving_review_with_rating_change_recalculates_old_book_too(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $firstBook = Book::factory()->create(['author_id' => $author->id]);
        $secondBook = Book::factory()->create(['author_id' => $author->id]);
        $review = Review::factory()->create(['user_id' => $user->id, 'book_id' => $firstBook->id, 'rating' => 5]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->put("/reviews/{$review->id}", [
                'userId' => $user->id,
                'bookId' => $secondBook->id,
                'rating' => 3,
                'comment' => 'Moved with new rating',
            ]);

        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'book_id' => $secondBook->id, 'rating' => 3]);

        $firstBook->refresh();
        $this->assertSame(0, (int) $firstBook->ratings_count);
        $this->assertEqualsWithDelta(0.0, (float) $firstBook->average_rating, 0.001);

        $secondBook->refresh();
        $this->assertSame(1, (int) $secondBook->ratings_count);
        $this->assertEqualsWithDelta(3.0, (float) $secondBook->average_rating, 0.001);
    }

    public function test_rating_only_update_recalculates_same_book_as_before(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);
        $review = Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'rating' => 5]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->put("/reviews/{$review->id}", [
                'userId' => $user->id,
                'bookId' => $book->id,
                'rating' => 2,
                'comment' => 'Rating only change',
            ]);

        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'book_id' => $book->id, 'rating' => 2]);

        $book->refresh();
        $this->assertSame(1, (int) $book->ratings_count);
        $this->assertEqualsWithDelta(2.0, (float) $book->average_rating, 0.001);
    }
}
