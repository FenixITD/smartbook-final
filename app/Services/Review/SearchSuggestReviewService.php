<?php

declare(strict_types=1);

namespace App\Services\Review;

use App\Dto\Review\ReviewResponseDto;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use App\Services\Abstracts\AbstractSearchSuggestService;

/**
 * @extends AbstractSearchSuggestService<ReviewResponseDto>
 */
class SearchSuggestReviewService extends AbstractSearchSuggestService
{
    public function __construct(
        ReviewRepositoryInterface $repository,
        SearchReviewByQueryService $searchService,
    ) {
        parent::__construct($repository, $searchService);
    }

    /**
     * @param mixed $entity
     * @return array<string, mixed>
     */
    protected function mapResult(mixed $entity): array
    {
        /** @var ReviewResponseDto $entity */
        return [
            'id' => $entity->id,
            'user_name' => $entity->userName,
            'content' => mb_substr($entity->comment, 0, 80).'...',
            'url' => route('reviews.show', $entity->id),
        ];
    }
}
