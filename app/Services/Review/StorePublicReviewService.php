<?php

declare(strict_types=1);

namespace App\Services\Review;

use App\Dto\Review\ReviewDto;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Validation\ValidationException;

final readonly class StorePublicReviewService
{
    public function __construct(
        private ReviewRepositoryInterface $repository,
    ) {
    }

    public function execute(ReviewDto $dto): void
    {
        $existing = $this->repository->findByUserAndBook($dto->userId, $dto->bookId);

        if ($existing !== null) {
            throw ValidationException::withMessages([
                'book_id' => 'You have already reviewed this book.',
            ]);
        }

        $this->repository->create($dto);
    }
}
