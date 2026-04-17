<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ReviewApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Book $book;

    // -----------------------------------------------------------------------
    // GET /api/reviews
    // -----------------------------------------------------------------------

    public function testGetListReturns200WithReviews(): void
    {
        Review::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->getJson('/api/reviews');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'userId', 'bookId', 'rating', 'comment', 'createdAt', 'updatedAt'],
                ],
            ]);
    }

    public function testGetListReturnsEmptyDataWhenNoReviews(): void
    {
        $response = $this->getJson('/api/reviews');

        $response->assertStatus(200)
            ->assertJson(['data' => []]);
    }

    public function testGetListRespectsPerPageParam(): void
    {
        Review::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->getJson('/api/reviews?perPage=4');

        $response->assertStatus(200);
        self::assertCount(4, $response->json('data'));
    }

    public function testGetListSortsByRatingDesc(): void
    {
        Review::factory()->create(['rating' => 1.0, 'user_id' => $this->user->id, 'book_id' => $this->book->id]);
        Review::factory()->create(['rating' => 5.0, 'user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $response = $this->getJson('/api/reviews?sortBy=rating&sortDirection=desc');

        $response->assertStatus(200);
        $data = $response->json('data');
        self::assertSame(5.0, $data[0]['rating']);
        self::assertSame(1.0, $data[1]['rating']);
    }

    public function testGetListValidatesSortDirection(): void
    {
        $response = $this->getJson('/api/reviews?sortDirection=invalid');

        $response->assertStatus(422);
    }

    public function testGetListValidatesPerPageMin(): void
    {
        $response = $this->getJson('/api/reviews?perPage=0');

        $response->assertStatus(422);
    }

    public function testGetListValidatesPerPageMax(): void
    {
        $response = $this->getJson('/api/reviews?perPage=101');

        $response->assertStatus(422);
    }

    // -----------------------------------------------------------------------
    // GET /api/reviews/{review}
    // -----------------------------------------------------------------------

    public function testGetByIdReturnsReview(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'rating' => 3.0,
        ]);

        $response = $this->getJson("/api/reviews/{$review->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'userId', 'bookId', 'rating', 'comment', 'createdAt', 'updatedAt'],
            ])
            ->assertJsonPath('data.id', $review->id)
            ->assertJsonPath('data.userId', $this->user->id);
    }

    public function testGetByIdReturns404ForNonexistentReview(): void
    {
        $response = $this->getJson('/api/reviews/99999');

        $response->assertStatus(404);
    }

    // -----------------------------------------------------------------------
    // POST /api/reviews
    // -----------------------------------------------------------------------

    public function testCreateReviewReturns201WithData(): void
    {
        $response = $this->postJson('/api/reviews', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'userId', 'bookId', 'rating', 'comment', 'createdAt', 'updatedAt'],
            ])
            ->assertJsonPath('data.userId', $this->user->id)
            ->assertJsonPath('data.bookId', $this->book->id);
    }

    public function testCreateReviewPersistsToDatabase(): void
    {
        $this->postJson('/api/reviews', $this->validPayload(['comment' => 'Excellent read!']));

        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'comment' => 'Excellent read!',
        ]);
    }

    public function testCreateReviewRequiresUserId(): void
    {
        $response = $this->postJson('/api/reviews', $this->validPayload(['userId' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['userId']);
    }

    public function testCreateReviewRequiresValidUserId(): void
    {
        $response = $this->postJson('/api/reviews', $this->validPayload(['userId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['userId']);
    }

    public function testCreateReviewRequiresBookId(): void
    {
        $response = $this->postJson('/api/reviews', $this->validPayload(['bookId' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bookId']);
    }

    public function testCreateReviewRequiresValidBookId(): void
    {
        $response = $this->postJson('/api/reviews', $this->validPayload(['bookId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bookId']);
    }

    public function testCreateReviewRequiresRating(): void
    {
        $response = $this->postJson('/api/reviews', $this->validPayload(['rating' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }

    public function testCreateReviewRatingCannotBeNegative(): void
    {
        $response = $this->postJson('/api/reviews', $this->validPayload(['rating' => -1]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }

    public function testCreateReviewRatingCannotExceedMax(): void
    {
        $response = $this->postJson('/api/reviews', $this->validPayload(['rating' => 5.1]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }

    public function testCreateReviewAcceptsBoundaryRatings(): void
    {
        $response = $this->postJson('/api/reviews', $this->validPayload(['rating' => 0]));
        $response->assertStatus(201);

        $response = $this->postJson('/api/reviews', $this->validPayload(['rating' => 5]));
        $response->assertStatus(201);
    }

    // -----------------------------------------------------------------------
    // PUT /api/reviews/{review}
    // -----------------------------------------------------------------------

    public function testUpdateReviewReturns200WithUpdatedData(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->putJson("/api/reviews/{$review->id}", $this->validPayload([
            'rating' => 2.0,
            'comment' => 'Updated opinion',
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('data.comment', 'Updated opinion');
    }

    public function testUpdateReviewPersistsChangesToDatabase(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $this->putJson("/api/reviews/{$review->id}", $this->validPayload([
            'comment' => 'Persisted comment',
        ]));

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'comment' => 'Persisted comment',
        ]);
    }

    public function testUpdateReviewReturns404ForNonexistentReview(): void
    {
        $response = $this->putJson('/api/reviews/99999', $this->validPayload());

        $response->assertStatus(404);
    }

    public function testUpdateReviewRequiresRating(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->putJson("/api/reviews/{$review->id}", $this->validPayload(['rating' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }

    public function testUpdateReviewRatingCannotExceedMax(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->putJson("/api/reviews/{$review->id}", $this->validPayload(['rating' => 6]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }

    public function testUpdateReviewRequiresValidUserId(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->putJson("/api/reviews/{$review->id}", $this->validPayload(['userId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['userId']);
    }

    public function testUpdateReviewRequiresValidBookId(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->putJson("/api/reviews/{$review->id}", $this->validPayload(['bookId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bookId']);
    }

    // -----------------------------------------------------------------------
    // DELETE /api/reviews/{review}
    // -----------------------------------------------------------------------

    public function testDeleteReviewReturns200WithMessage(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Review deleted successfully']);
    }

    public function testDeleteReviewRemovesFromDatabase(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $this->deleteJson("/api/reviews/{$review->id}");

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function testDeleteReviewReturns404ForNonexistentReview(): void
    {
        $response = $this->deleteJson('/api/reviews/99999');

        $response->assertStatus(404);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->book = Book::factory()->create(['author_id' => Author::factory()->create()->id]);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'userId' => $this->user->id,
            'bookId' => $this->book->id,
            'rating' => 4.5,
            'comment' => 'Great book!',
        ], $overrides);
    }
}
