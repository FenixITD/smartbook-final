<?php

declare(strict_types=1);

namespace App\Services\Review;

use App\Dto\Review\ReviewResponseDto;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;

class SearchSuggestReviewService
{
    public function __construct(
        private ReviewRepositoryInterface $repository,
        private SearchReviewByQueryService $searchService,
    ) {
    }

    /**
     * @param string $query
     * @return array
     * @throws ClientResponseException
     * @throws ServerResponseException
     *
     * Fetches up to 5 review suggestions for autocomplete search, returning the author's name, a truncated comment, and the URL.
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
