<?php

declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\Review\ReviewDto;
use App\Dto\Review\ReviewFiltersDto;
use App\Dto\Review\ReviewResponseDto;
use App\Models\Review;
use Tests\TestCase;

class ReviewDtoTest extends TestCase
{
    public function test_review_dto_to_array_returns_correct_structure(): void
    {
        $dto = new ReviewDto(
            userId: 1,
            bookId: 2,
            rating: 4.5,
            comment: 'Great book!',
        );

        $result = $dto->toArray();

        $this->assertSame([
            'user_id' => 1,
            'book_id' => 2,
            'rating' => 4.5,
            'comment' => 'Great book!',
        ], $result);
    }

    public function test_review_dto_stores_properties_correctly(): void
    {
        $dto = new ReviewDto(
            userId: 5,
            bookId: 10,
            rating: 3.0,
            comment: 'Not bad.',
        );

        $this->assertSame(5, $dto->userId);
        $this->assertSame(10, $dto->bookId);
        $this->assertSame(3.0, $dto->rating);
        $this->assertSame('Not bad.', $dto->comment);
    }

    public function test_review_filters_dto_has_correct_defaults(): void
    {
        $dto = new ReviewFiltersDto;

        $this->assertNull($dto->search);
        $this->assertSame(15, $dto->perPage);
        $this->assertSame('id', $dto->sortBy);
        $this->assertSame('asc', $dto->sortDirection);
    }

    public function test_review_filters_dto_accepts_custom_values(): void
    {
        $dto = new ReviewFiltersDto(
            search: '5',
            perPage: 20,
            sortBy: 'rating',
            sortDirection: 'desc',
        );

        $this->assertSame('5', $dto->search);
        $this->assertSame(20, $dto->perPage);
        $this->assertSame('rating', $dto->sortBy);
        $this->assertSame('desc', $dto->sortDirection);
    }

    public function test_review_response_dto_from_model(): void
    {
        $review = new Review;
        $review->id = 7;
        $review->user_id = 3;
        $review->book_id = 12;
        $review->rating = 4.0;
        $review->comment = 'Really enjoyed it.';
        $review->created_at = now()->setDateTimeFrom('2024-02-01 08:00:00');
        $review->updated_at = now()->setDateTimeFrom('2024-02-10 14:00:00');

        $dto = ReviewResponseDto::fromModel($review);

        $this->assertSame(7, $dto->id);
        $this->assertSame(3, $dto->userId);
        $this->assertSame(12, $dto->bookId);
        $this->assertSame(4.0, $dto->rating);
        $this->assertSame('Really enjoyed it.', $dto->comment);
        $this->assertSame('2024-02-01 08:00:00', $dto->createdAt);
        $this->assertSame('2024-02-10 14:00:00', $dto->updatedAt);
    }

    public function test_review_response_dto_casts_fields_to_correct_types(): void
    {
        $review = new Review;
        $review->id = 1;
        $review->user_id = 1;
        $review->book_id = 1;
        $review->rating = 5.0;
        $review->comment = 'Perfect.';
        $review->created_at = now();
        $review->updated_at = now();

        $dto = ReviewResponseDto::fromModel($review);

        $this->assertIsInt($dto->userId);
        $this->assertIsInt($dto->bookId);
        $this->assertIsFloat($dto->rating);
        $this->assertIsString($dto->comment);
    }
}
