<?php

declare(strict_types=1);

namespace App\Services\Author;

use App\Services\Abstracts\AbstractSearchService;

class SearchAuthorService extends AbstractSearchService
{
    protected function getIndexConfigKey(): string
    {
        return 'elasticsearch.authors_index';
    }
}
