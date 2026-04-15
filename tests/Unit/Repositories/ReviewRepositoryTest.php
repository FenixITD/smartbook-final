<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Dto\Review\ReviewDto;
use App\Dto\Review\ReviewFiltersDto;
use App\Dto\Review\ReviewResponseDto;
use App\Models\Author;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use App\Repositories\Eloquent\ReviewRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ReviewRepository $repository;

    private User $user;

    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ReviewRepository;
        $this->user = User::factory()->create();
        $this->book = Book::factory()->create(['author_id' => Author::factory()->create()->id]);
    }

    private function makeDto(array $overrides = []): ReviewDto
    {
        return new ReviewDto(
            userId: $overrides['userId'] ?? $this->user->id,
            bookId: $overrides['bookId'] ?? $this->book->id,
            rating: $overrides['rating'] ?? 4.5,
            comment: $overrides['comment'] ?? 'Great book!',
        );
    }

    // -----------------------------------------------------------------------
    // getList
    // -----------------------------------------------------------------------

    public function test_get_list_returns_array_of_review_response_dtos(): void
    {
        Review::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $filters = new ReviewFiltersDto;
        $result = $this->repository->getList($filters);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertContainsOnlyInstancesOf(ReviewResponseDto::class, $result);
    }

    public function test_get_list_returns_empty_array_when_no_reviews(): void
    {
        $filters = new ReviewFiltersDto;
        $result = $this->repository->getList($filters);

        $this->assertSame([], $result);
    }

    public function test_get_list_respects_per_page(): void
    {
        Review::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $filters = new ReviewFiltersDto(perPage: 4);
        $result = $this->repository->getList($filters);

        $this->assertCount(4, $result);
    }

    public function test_get_list_sorts_by_rating_asc(): void
    {
        Review::factory()->create(['rating' => 5.0, 'user_id' => $this->user->id, 'book_id' => $this->book->id]);
        Review::factory()->create(['rating' => 1.0, 'user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $filters = new ReviewFiltersDto(sortBy: 'rating', sortDirection: 'asc');
        $result = $this->repository->getList($filters);

        $this->assertEquals(1.0, $result[0]->rating);
        $this->assertEquals(5.0, $result[1]->rating);
    }

    public function test_get_list_sorts_by_rating_desc(): void
    {
        Review::factory()->create(['rating' => 1.0, 'user_id' => $this->user->id, 'book_id' => $this->book->id]);
        Review::factory()->create(['rating' => 5.0, 'user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $filters = new ReviewFiltersDto(sortBy: 'rating', sortDirection: 'desc');
        $result = $this->repository->getList($filters);

        $this->assertEquals(5.0, $result[0]->rating);
        $this->assertEquals(1.0, $result[1]->rating);
    }

    // -----------------------------------------------------------------------
    // getById
    // -----------------------------------------------------------------------

    public function test_get_by_id_returns_review_response_dto(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'comment' => 'Wonderful.',
        ]);

        $result = $this->repository->getById($review->id);

        $this->assertInstanceOf(ReviewResponseDto::class, $result);
        $this->assertSame($review->id, $result->id);
        $this->assertSame('Wonderful.', $result->comment);
    }

    public function test_get_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->getById(99999);

        $this->assertNull($result);
    }

    // -----------------------------------------------------------------------
    // create
    // -----------------------------------------------------------------------

    public function test_create_persists_review_and_returns_dto(): void
    {
        $dto = $this->makeDto(['comment' => 'Loved it!']);

        $result = $this->repository->create($dto);

        $this->assertInstanceOf(ReviewResponseDto::class, $result);
        $this->assertSame('Loved it!', $result->comment);
        $this->assertDatabaseHas('reviews', ['comment' => 'Loved it!']);
    }

    public function test_create_assigns_id_to_returned_dto(): void
    {
        $dto = $this->makeDto();

        $result = $this->repository->create($dto);

        $this->assertIsInt($result->id);
        $this->assertGreaterThan(0, $result->id);
    }

    public function test_create_stores_all_fields_correctly(): void
    {
        $dto = $this->makeDto([
            'rating' => 2.5,
            'comment' => 'Average book.',
        ]);

        $result = $this->repository->create($dto);

        $this->assertSame($this->user->id, $result->userId);
        $this->assertSame($this->book->id, $result->bookId);
        $this->assertEquals(2.5, $result->rating);
        $this->assertSame('Average book.', $result->comment);
    }

    // -----------------------------------------------------------------------
    // update
    // -----------------------------------------------------------------------

    public function test_update_changes_review_fields_and_returns_dto(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'comment' => 'Old comment',
        ]);
        $dto = $this->makeDto(['comment' => 'New comment']);

        $result = $this->repository->update($review->id, $dto);

        $this->assertInstanceOf(ReviewResponseDto::class, $result);
        $this->assertSame('New comment', $result->comment);
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'comment' => 'New comment']);
    }

    public function test_update_does_not_create_new_record(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);
        $dto = $this->makeDto(['comment' => 'Updated']);

        $this->repository->update($review->id, $dto);

        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_update_throws_exception_for_nonexistent_review(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->update(99999, $this->makeDto());
    }

    // -----------------------------------------------------------------------
    // delete
    // -----------------------------------------------------------------------

    public function test_delete_removes_review_from_database(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $result = $this->repository->delete($review->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_delete_returns_true_on_success(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $result = $this->repository->delete($review->id);

        $this->assertTrue($result);
    }

    public function test_delete_returns_false_for_nonexistent_review(): void
    {
        $result = $this->repository->delete(99999);

        $this->assertFalse($result);
    }
}
