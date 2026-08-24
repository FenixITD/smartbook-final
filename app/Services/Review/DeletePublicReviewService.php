<?php

declare(strict_types=1);

namespace App\Services\Review;

use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\ReviewRepositoryInterface;

final readonly class DeletePublicReviewService
{
    public function __construct(
        private ReviewRepositoryInterface $repository,
        private TransactionManagerInterface $transactionManager,
    ) {}

    public function execute(int $reviewId, int $userId): void
    {
        $review = $this->repository->getById($reviewId);

        if ($review === null || $review->userId !== $userId) {
            abort(403, 'Unauthorized action.');
        }

        $this->transactionManager->transaction(
            fn (): bool => $this->repository->delete($reviewId)
        );
    }
}
