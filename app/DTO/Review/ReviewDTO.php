<?php

declare(strict_types=1);

namespace App\DTO\Review;

use App\Http\Requests\Review\ReviewDataRequest;

final readonly class ReviewDTO
{
    public function __construct(
        public int $userId,
        public int $bookId,
        public float $rating,
        public string $comment,
    ) {}

    public static function fromRequest(ReviewDataRequest $request): self
    {
        return new self(
            userId: $request->integer('userId'),
            bookId: $request->integer('bookId'),
            rating: $request->float('rating'),
            comment: (string) $request->string('comment'),
        );
    }
}
