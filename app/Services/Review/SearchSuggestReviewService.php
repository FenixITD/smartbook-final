<?php

declare(strict_types=1);

namespace App\Services\Review;

use App\Dto\Review\ReviewResponseDto;
use App\Repositories\Interfaces\ReviewRepositoryInterface;

final readonly class SearchSuggestReviewService
{
    public function __construct(
        private ReviewRepositoryInterface $repository,
        private SearchReviewByQueryService $searchService,
    ) {
    }

    /**
     * @return array<int, array{id: int, content: string, url: string}>
     */
    public function execute(string $query): array
    {
        $ids = $this->searchService->search($query, limit: 5);

        if ($ids === []) {
            return [];
        }

        return array_values(array_map(
            static fn (ReviewResponseDto $review): array => [
                'id' => $review->id,
                'user_name' => $review->userName,
                'content' => mb_substr($review->comment, 0, 80) . '...',
                'url' => route('reviews.show', $review->id),
            ],
            $this->repository->getByIds($ids),
        ));
    }
}
