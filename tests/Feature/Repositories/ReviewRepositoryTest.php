<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Dto\PaginatedResponseDto;
use App\Dto\Review\BookReviewResponseDto;
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ReviewRepository();
    }

    private function makeUser(): User
    {
        return User::factory()->create();
    }

    private function makeBook(): Book
    {
        $author = Author::factory()->create();
        return Book::factory()->create(['author_id' => $author->id]);
    }

    private function makeReview(array $attributes = []): Review
    {
        $attributes['user_id'] ??= $this->makeUser()->id;
        $attributes['book_id'] ??= $this->makeBook()->id;
        return Review::factory()->create($attributes);
    }

    public function test_get_list_returns_array_of_review_response_dtos(): void
    {
        $this->makeReview();
        $this->makeReview();

        $filters = new ReviewFiltersDto();
        $result = $this->repository->getList($filters);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(ReviewResponseDto::class, $result);
    }

    public function test_get_list_filters_by_id(): void
    {
        $review = $this->makeReview();
        $this->makeReview();

        $filters = new ReviewFiltersDto(id: $review->id);
        $result = $this->repository->getList($filters);

        $this->assertCount(1, $result);
        $this->assertSame($review->id, $result[0]->id);
    }

    public function test_get_list_respects_sort_direction(): void
    {
        $user = $this->makeUser();
        $book = $this->makeBook();

        $first = $this->makeReview(['user_id' => $user->id, 'book_id' => $book->id]);
        $second = $this->makeReview(['user_id' => $this->makeUser()->id, 'book_id' => $book->id]);

        $filters = new ReviewFiltersDto(sortBy: 'id', sortDirection: 'desc');
        $result = $this->repository->getList($filters);

        $this->assertSame($second->id, $result[0]->id);
        $this->assertSame($first->id, $result[1]->id);
    }

    public function test_get_web_list_by_ids_returns_paginated_response(): void
    {
        $reviews = collect([
            $this->makeReview(),
            $this->makeReview(),
            $this->makeReview(),
        ]);

        $ids = $reviews->pluck('id')->all();
        $filters = new ReviewFiltersDto(perPage: 10);

        $result = $this->repository->getWebListByIds($ids, count($ids), $filters);

        $this->assertInstanceOf(PaginatedResponseDto::class, $result);
        $this->assertSame(3, $result->total);
    }

    public function test_get_web_list_by_ids_excludes_other_reviews(): void
    {
        $included = $this->makeReview();
        $this->makeReview();

        $filters = new ReviewFiltersDto(perPage: 10);
        $result = $this->repository->getWebListByIds([$included->id], is_countable([$included->id]) ? count([$included->id]) : (is_array([$included->id]) ? count([$included->id]) : 0), $filters);

        $this->assertSame(1, $result->total);
    }

    public function test_get_by_id_returns_dto_when_found(): void
    {
        $review = $this->makeReview();

        $result = $this->repository->getById($review->id);

        $this->assertInstanceOf(ReviewResponseDto::class, $result);
        $this->assertSame($review->id, $result->id);
    }

    public function test_get_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->getById(99999);

        $this->assertNull($result);
    }

    public function test_find_by_id_with_relations_returns_dto(): void
    {
        $review = $this->makeReview();

        $result = $this->repository->findByIdWithRelations($review->id);

        $this->assertInstanceOf(ReviewResponseDto::class, $result);
        $this->assertSame($review->id, $result->id);
    }

    public function test_find_by_id_with_relations_throws_when_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->findByIdWithRelations(99999);
    }

    public function test_get_by_ids_returns_matching_reviews(): void
    {
        $r1 = $this->makeReview();
        $r2 = $this->makeReview();
        $this->makeReview();

        $result = $this->repository->getByIds([$r1->id, $r2->id]);

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(ReviewResponseDto::class, $result);
        $resultIds = array_map(static fn (ReviewResponseDto $dto) => $dto->id, $result);
        $this->assertContains($r1->id, $resultIds);
        $this->assertContains($r2->id, $resultIds);
    }

    public function test_create_persists_review_and_returns_dto(): void
    {
        $user = $this->makeUser();
        $book = $this->makeBook();

        $dto = new ReviewDto(
            userId: $user->id,
            bookId: $book->id,
            rating: 4.5,
            comment: 'Excellent read',
        );

        $result = $this->repository->create($dto);

        $this->assertInstanceOf(ReviewResponseDto::class, $result);
        $this->assertSame($user->id, $result->userId);
        $this->assertSame($book->id, $result->bookId);
        $this->assertSame(4.5, $result->rating);
        $this->assertSame('Excellent read', $result->comment);
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'comment' => 'Excellent read',
        ]);
    }

    public function test_update_changes_review_and_returns_dto(): void
    {
        $review = $this->makeReview(['rating' => 3, 'comment' => 'Old comment']);

        $dto = new ReviewDto(
            userId: $review->user_id,
            bookId: $review->book_id,
            rating: 5.0,
            comment: 'Updated comment',
        );

        $result = $this->repository->update($review->id, $dto);

        $this->assertInstanceOf(ReviewResponseDto::class, $result);
        $this->assertSame(5.0, $result->rating);
        $this->assertSame('Updated comment', $result->comment);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'comment' => 'Updated comment',
        ]);
    }

    public function test_update_throws_when_review_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $dto = new ReviewDto(userId: 1, bookId: 1, rating: 3.0, comment: 'x');
        $this->repository->update(99999, $dto);
    }

    public function test_delete_removes_review_from_database(): void
    {
        $review = $this->makeReview();

        $result = $this->repository->delete($review->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_delete_throws_when_review_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->delete(99999);
    }

    public function test_get_by_book_id_returns_paginated_response(): void
    {
        $book = $this->makeBook();
        $user = $this->makeUser();

        $this->makeReview(['book_id' => $book->id, 'user_id' => $user->id]);
        $this->makeReview(['book_id' => $book->id, 'user_id' => $this->makeUser()->id]);
        $this->makeReview();

        $result = $this->repository->getByBookId($book->id);

        $this->assertInstanceOf(PaginatedResponseDto::class, $result);
        $this->assertSame(2, $result->total);
    }

    public function test_get_by_book_id_items_are_book_review_response_dtos(): void
    {
        $book = $this->makeBook();
        $user = $this->makeUser();

        $this->makeReview(['book_id' => $book->id, 'user_id' => $user->id]);

        $result = $this->repository->getByBookId($book->id);

        $this->assertNotEmpty($result->items);
        $this->assertContainsOnlyInstancesOf(BookReviewResponseDto::class, $result->items);
    }

    public function test_get_by_book_id_respects_per_page(): void
    {
        $book = $this->makeBook();
        $user = $this->makeUser();

        for ($i = 0; $i < 5; $i++) { $this->makeReview(['book_id' => $book->id, 'user_id' => $this->makeUser()->id]); }

        $result = $this->repository->getByBookId($book->id, perPage: 2);

        $this->assertSame(5, $result->total);
        $this->assertSame(2, $result->perPage);
        $this->assertCount(2, $result->items);
    }

    public function test_get_by_book_id_returns_empty_for_unknown_book(): void
    {
        $result = $this->repository->getByBookId(99999);

        $this->assertSame(0, $result->total);
        $this->assertEmpty($result->items);
    }

    public function test_review_response_dto_contains_correct_user_name(): void
    {
        $user = $this->makeUser();
        $book = $this->makeBook();
        $review = $this->makeReview(['user_id' => $user->id, 'book_id' => $book->id]);

        $result = $this->repository->getById($review->id);

        $this->assertSame($user->name, $result->userName);
    }
}
