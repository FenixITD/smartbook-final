<?php

declare(strict_types=1);

namespace App\Dto\Review;

use App\Models\Review;

final readonly class ReviewResponseDto
{
    public static function fromModel(Review $review): self
    {
        return new self(
            id: $review->id,
            userId: $review->user_id,
            bookId: $review->book_id,
            rating: $review->rating,
            comment: $review->comment ?? '',
            createdAt: $review->created_at?->toDateTimeString() ?? '',
            updatedAt: $review->updated_at?->toDateTimeString() ?? '',
        );
    }

    public function __construct(
        public int $id,
        public int $userId,
        public int $bookId,
        public float $rating,
        public string $comment,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }
}
