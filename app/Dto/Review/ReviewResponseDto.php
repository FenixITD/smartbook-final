<?php

declare(strict_types=1);

namespace App\Dto\Review;

use App\Models\Review;

class ReviewResponseDto
{
    public function __construct(
        public int $id,
        public int $userId,
        public int $bookId,
        public string $userName,
        public float $rating,
        public string $comment,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromModel(Review $review): self
    {
        return new self(
            id: $review->id,
            userId: $review->user_id,
            bookId: $review->book_id,
            userName: $review->user?->name ?? '',
            rating: $review->rating,
            comment: $review->comment ?? '',
            createdAt: $review->created_at?->toDateTimeString() ?? '',
            updatedAt: $review->updated_at?->toDateTimeString() ?? '',
        );
    }
}
