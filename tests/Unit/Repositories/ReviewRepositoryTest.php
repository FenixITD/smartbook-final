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

/**
 * @internal
 *
 * @coversNothing
 */
final class ReviewRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ReviewRepository $repository;

    private User $user;

    private Book $book;

    // -----------------------------------------------------------------------
    // getList
    // -----------------------------------------------------------------------

    public function testGetListReturnsArrayOfReviewResponseDtos(): void
    {
        Review::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $filters = new ReviewFiltersDto();
        $result = $this->repository->getList($filters);

        self::assertIsArray($result);
        self::assertCount(3, $result);
        self::assertContainsOnlyInstancesOf(ReviewResponseDto::class, $result);
    }

    public function testGetListReturnsEmptyArrayWhenNoReviews(): void
    {
        $filters = new ReviewFiltersDto();
        $result = $this->repository->getList($filters);

        self::assertSame([], $result);
    }

    public function testGetListRespectsPerPage(): void
    {
        Review::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $filters = new ReviewFiltersDto(perPage: 4);
        $result = $this->repository->getList($filters);

        self::assertCount(4, $result);
    }

    public function testGetListSortsByRatingAsc(): void
    {
        Review::factory()->create(['rating' => 5.0, 'user_id' => $this->user->id, 'book_id' => $this->book->id]);
        Review::factory()->create(['rating' => 1.0, 'user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $filters = new ReviewFiltersDto(sortBy: 'rating', sortDirection: 'asc');
        $result = $this->repository->getList($filters);

        self::assertSame(1.0, $result[0]->rating);
        self::assertSame(5.0, $result[1]->rating);
    }

    public function testGetListSortsByRatingDesc(): void
    {
        Review::factory()->create(['rating' => 1.0, 'user_id' => $this->user->id, 'book_id' => $this->book->id]);
        Review::factory()->create(['rating' => 5.0, 'user_id' => $this->user->id, 'book_id' => $this->book->id]);

        $filters = new ReviewFiltersDto(sortBy: 'rating', sortDirection: 'desc');
        $result = $this->repository->getList($filters);

        self::assertSame(5.0, $result[0]->rating);
        self::assertSame(1.0, $result[1]->rating);
    }

    // -----------------------------------------------------------------------
    // getById
    // -----------------------------------------------------------------------

    public function testGetByIdReturnsReviewResponseDto(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'comment' => 'Wonderful.',
        ]);

        $result = $this->repository->getById($review->id);

        self::assertInstanceOf(ReviewResponseDto::class, $result);
        self::assertSame($review->id, $result->id);
        self::assertSame('Wonderful.', $result->comment);
    }

    public function testGetByIdReturnsNullWhenNotFound(): void
    {
        $result = $this->repository->getById(99999);

        self::assertNull($result);
    }

    // -----------------------------------------------------------------------
    // create
    // -----------------------------------------------------------------------

    public function testCreatePersistsReviewAndReturnsDto(): void
    {
        $dto = $this->makeDto(['comment' => 'Loved it!']);

        $result = $this->repository->create($dto);

        self::assertInstanceOf(ReviewResponseDto::class, $result);
        self::assertSame('Loved it!', $result->comment);
        $this->assertDatabaseHas('reviews', ['comment' => 'Loved it!']);
    }

    public function testCreateAssignsIdToReturnedDto(): void
    {
        $dto = $this->makeDto();

        $result = $this->repository->create($dto);

        self::assertIsInt($result->id);
        self::assertGreaterThan(0, $result->id);
    }

    public function testCreateStoresAllFieldsCorrectly(): void
    {
        $dto = $this->makeDto([
            'rating' => 2.5,
            'comment' => 'Average book.',
        ]);

        $result = $this->repository->create($dto);

        self::assertSame($this->user->id, $result->userId);
        self::assertSame($this->book->id, $result->bookId);
        self::assertSame(2.5, $result->rating);
        self::assertSame('Average book.', $result->comment);
    }

    // -----------------------------------------------------------------------
    // update
    // -----------------------------------------------------------------------

    public function testUpdateChangesReviewFieldsAndReturnsDto(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'comment' => 'Old comment',
        ]);
        $dto = $this->makeDto(['comment' => 'New comment']);

        $result = $this->repository->update($review->id, $dto);

        self::assertInstanceOf(ReviewResponseDto::class, $result);
        self::assertSame('New comment', $result->comment);
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'comment' => 'New comment']);
    }

    public function testUpdateDoesNotCreateNewRecord(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);
        $dto = $this->makeDto(['comment' => 'Updated']);

        $this->repository->update($review->id, $dto);

        $this->assertDatabaseCount('reviews', 1);
    }

    public function testUpdateThrowsExceptionForNonexistentReview(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->update(99999, $this->makeDto());
    }

    // -----------------------------------------------------------------------
    // delete
    // -----------------------------------------------------------------------

    public function testDeleteRemovesReviewFromDatabase(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $result = $this->repository->delete($review->id);

        self::assertTrue($result);
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function testDeleteReturnsTrueOnSuccess(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $result = $this->repository->delete($review->id);

        self::assertTrue($result);
    }

    public function testDeleteReturnsFalseForNonexistentReview(): void
    {
        $result = $this->repository->delete(99999);

        self::assertFalse($result);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ReviewRepository();
        $this->user = User::factory()->create();
        $this->book = Book::factory()->create(['author_id' => Author::factory()->create()->id]);
    }

    /** @param array<string, mixed> $overrides */
    private function makeDto(array $overrides = []): ReviewDto
    {
        return new ReviewDto(
            userId: (int) ($overrides['userId'] ?? $this->user->id),
            bookId: (int) ($overrides['bookId'] ?? $this->book->id),
            rating: (float) ($overrides['rating'] ?? 4.5),
            comment: (string) ($overrides['comment'] ?? 'Great book!'),
        );
    }
}
