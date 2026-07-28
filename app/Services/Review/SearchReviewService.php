<?php

declare(strict_types=1);

namespace App\Services\Review;

use App\Services\Abstracts\AbstractSearchService;

class SearchReviewService extends AbstractSearchService
{
    protected function getIndexConfigKey(): string
    {
        return 'elasticsearch.reviews_index';
    }

    /**
     * @return array<int, string>
     */
    protected function getSearchFields(): array
    {
        return ['user_name^3','comment'];
    }
}
