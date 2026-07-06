<?php

declare(strict_types=1);

namespace App\Services\Review;

use App\Services\Abstracts\AbstractSearchByQueryService;

class SearchReviewByQueryService extends AbstractSearchByQueryService
{
    protected function getIndexConfigKey(): string
    {
        return 'elasticsearch.reviews_index';
    }
}
