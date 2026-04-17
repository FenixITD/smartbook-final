<?php

declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\Review\ReviewDto;
use App\Dto\Review\ReviewFiltersDto;
use App\Dto\Review\ReviewResponseDto;
use App\Models\Review;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ReviewDtoTest extends TestCase
{
    public function testReviewDtoToArrayReturnsCorrectStructure(): void
    {
        $dto = new ReviewDto(
            userId: 1,
            bookId: 2,
            rating: 4.5,
            comment: 'Great book!',
        );

        $result = $dto->toArray();

        self::assertSame([
            'user_id' => 1,
            'book_id' => 2,
            'rating' => 4.5,
            'comment' => 'Great book!',
        ], $result);
    }

    public function testReviewDtoStoresPropertiesCorrectly(): void
    {
        $dto = new ReviewDto(
            userId: 5,
            bookId: 10,
            rating: 3.0,
            comment: 'Not bad.',
        );

        self::assertSame(5, $dto->userId);
        self::assertSame(10, $dto->bookId);
        self::assertSame(3.0, $dto->rating);
        self::assertSame('Not bad.', $dto->comment);
    }

    public function testReviewFiltersDtoHasCorrectDefaults(): void
    {
        $dto = new ReviewFiltersDto();

        self::assertNull($dto->search);
        self::assertSame(15, $dto->perPage);
        self::assertSame('id', $dto->sortBy);
        self::assertSame('asc', $dto->sortDirection);
    }

    public function testReviewFiltersDtoAcceptsCustomValues(): void
    {
        $dto = new ReviewFiltersDto(
            search: '5',
            perPage: 20,
            sortBy: 'rating',
            sortDirection: 'desc',
        );

        self::assertSame('5', $dto->search);
        self::assertSame(20, $dto->perPage);
        self::assertSame('rating', $dto->sortBy);
        self::assertSame('desc', $dto->sortDirection);
    }

    public function testReviewResponseDtoFromModel(): void
    {
        $review = new Review();
        $review->id = 7;
        $review->user_id = 3;
        $review->book_id = 12;
        $review->rating = 4.0;
        $review->comment = 'Really enjoyed it.';
        $review->created_at = now()->setDateTimeFrom('2024-02-01 08:00:00');
        $review->updated_at = now()->setDateTimeFrom('2024-02-10 14:00:00');

        $dto = ReviewResponseDto::fromModel($review);

        self::assertSame(7, $dto->id);
        self::assertSame(3, $dto->userId);
        self::assertSame(12, $dto->bookId);
        self::assertSame(4.0, $dto->rating);
        self::assertSame('Really enjoyed it.', $dto->comment);
        self::assertSame('2024-02-01 08:00:00', $dto->createdAt);
        self::assertSame('2024-02-10 14:00:00', $dto->updatedAt);
    }

    public function testReviewResponseDtoCastsFieldsToCorrectTypes(): void
    {
        $review = new Review();
        $review->id = 1;
        $review->user_id = 1;
        $review->book_id = 1;
        $review->rating = 5.0;
        $review->comment = 'Perfect.';
        $review->created_at = now();
        $review->updated_at = now();

        $dto = ReviewResponseDto::fromModel($review);

        self::assertIsInt($dto->userId);
        self::assertIsInt($dto->bookId);
        self::assertIsFloat($dto->rating);
        self::assertIsString($dto->comment);
    }
}
