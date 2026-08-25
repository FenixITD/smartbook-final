<?php

declare(strict_types=1);

namespace App\Services\Review;

use App\Dto\Review\ReviewDto;
use App\Dto\Review\ReviewResponseDto;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

final readonly class StorePublicReviewService
{
    public function __construct(
        private ReviewRepositoryInterface $repository,
    ) {
    }

    public function execute(ReviewDto $dto): ReviewResponseDto
    {
        $existing = $this->repository->findByUserAndBook($dto->userId, $dto->bookId);

        if ($existing !== null) {
            throw ValidationException::withMessages([
                'book_id' => 'You have already reviewed this book.',
            ]);
        }

        try {
            return $this->repository->create($dto);
        } catch (QueryException $e) {
            if ($e->errorInfo[1] === 1062) {
                throw ValidationException::withMessages([
                    'book_id' => 'You have already reviewed this book.',
                ]);
            }

            throw $e;
        }
    }
}
