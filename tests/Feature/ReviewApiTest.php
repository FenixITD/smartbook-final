<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->book = Book::factory()->create(['author_id' => Author::factory()->create()->id]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'userId' => $this->user->id,
            'bookId' => $this->book->id,
            'rating' => 4.5,
            'comment' => 'Great book!',
        ], $overrides);
    }

    // -----------------------------------------------------------------------
    // GET /api/reviews
    // -----------------------------------------------------------------------

    public function test_get_list_returns_200_with_reviews(): void
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

    public function test_get_list_returns_empty_data_when_no_reviews(): void
    {
        $response = $this->getJson('/api/reviews');

        $response->assertStatus(200)
            ->assertJson(['data' => []]);
    }

    public function test_get_list_respects_per_page_param(): void
    {
        Review::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->getJson('/api/reviews?perPage=4');

        $response->assertStatus(200);
        $this->assertCount(4, $response->json('data'));
    }

    public function test_get_list_sorts_by_rating_desc(): void
    {
        Review::factory()->create(['rating' => 1.0, 'user_id' => $this->user->id, 'book_id' => $this->book->id]);
        Review::factory()->create(['rating' => 5.0, 'user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $response = $this->getJson('/api/reviews?sortBy=rating&sortDirection=desc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals(5.0, $data[0]['rating']);
        $this->assertEquals(1.0, $data[1]['rating']);
    }

    public function test_get_list_validates_sort_direction(): void
    {
        $response = $this->getJson('/api/reviews?sortDirection=invalid');

        $response->assertStatus(422);
    }

    public function test_get_list_validates_per_page_min(): void
    {
        $response = $this->getJson('/api/reviews?perPage=0');

        $response->assertStatus(422);
    }

    public function test_get_list_validates_per_page_max(): void
    {
        $response = $this->getJson('/api/reviews?perPage=101');

        $response->assertStatus(422);
    }

    // -----------------------------------------------------------------------
    // GET /api/reviews/{review}
    // -----------------------------------------------------------------------

    public function test_get_by_id_returns_review(): void
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

    public function test_get_by_id_returns_404_for_nonexistent_review(): void
    {
        $response = $this->getJson('/api/reviews/99999');

        $response->assertStatus(404);
    }

    // -----------------------------------------------------------------------
    // POST /api/reviews
    // -----------------------------------------------------------------------

    public function test_create_review_returns_201_with_data(): void
    {
        $response = $this->postJson('/api/reviews', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'userId', 'bookId', 'rating', 'comment', 'createdAt', 'updatedAt'],
            ])
            ->assertJsonPath('data.userId', $this->user->id)
            ->assertJsonPath('data.bookId', $this->book->id);
    }

    public function test_create_review_persists_to_database(): void
    {
        $this->postJson('/api/reviews', $this->validPayload(['comment' => 'Excellent read!']));

        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'comment' => 'Excellent read!',
        ]);
    }

    public function test_create_review_requires_user_id(): void
    {
        $response = $this->postJson('/api/reviews', $this->validPayload(['userId' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['userId']);
    }

    public function test_create_review_requires_valid_user_id(): void
    {
        $response = $this->postJson('/api/reviews', $this->validPayload(['userId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['userId']);
    }

    public function test_create_review_requires_book_id(): void
    {
        $response = $this->postJson('/api/reviews', $this->validPayload(['bookId' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bookId']);
    }

    public function test_create_review_requires_valid_book_id(): void
    {
        $response = $this->postJson('/api/reviews', $this->validPayload(['bookId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bookId']);
    }

    public function test_create_review_requires_rating(): void
    {
        $response = $this->postJson('/api/reviews', $this->validPayload(['rating' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }

    public function test_create_review_rating_cannot_be_negative(): void
    {
        $response = $this->postJson('/api/reviews', $this->validPayload(['rating' => -1]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }

    public function test_create_review_rating_cannot_exceed_max(): void
    {
        $response = $this->postJson('/api/reviews', $this->validPayload(['rating' => 5.1]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }

    public function test_create_review_accepts_boundary_ratings(): void
    {
        $response = $this->postJson('/api/reviews', $this->validPayload(['rating' => 0]));
        $response->assertStatus(201);

        $response = $this->postJson('/api/reviews', $this->validPayload(['rating' => 5]));
        $response->assertStatus(201);
    }

    // -----------------------------------------------------------------------
    // PUT /api/reviews/{review}
    // -----------------------------------------------------------------------

    public function test_update_review_returns_200_with_updated_data(): void
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

    public function test_update_review_persists_changes_to_database(): void
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

    public function test_update_review_returns_404_for_nonexistent_review(): void
    {
        $response = $this->putJson('/api/reviews/99999', $this->validPayload());

        $response->assertStatus(404);
    }

    public function test_update_review_requires_rating(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->putJson("/api/reviews/{$review->id}", $this->validPayload(['rating' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }

    public function test_update_review_rating_cannot_exceed_max(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->putJson("/api/reviews/{$review->id}", $this->validPayload(['rating' => 6]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }

    public function test_update_review_requires_valid_user_id(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->putJson("/api/reviews/{$review->id}", $this->validPayload(['userId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['userId']);
    }

    public function test_update_review_requires_valid_book_id(): void
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

    public function test_delete_review_returns_200_with_message(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Review deleted successfully']);
    }

    public function test_delete_review_removes_from_database(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $this->deleteJson("/api/reviews/{$review->id}");

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_delete_review_returns_404_for_nonexistent_review(): void
    {
        $response = $this->deleteJson('/api/reviews/99999');

        $response->assertStatus(404);
    }
}
