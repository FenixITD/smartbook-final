<?php

declare(strict_types=1);

namespace App\Services\Author;

use App\Services\Abstracts\AbstractSearchByQueryService;

class SearchAuthorByQueryService extends AbstractSearchByQueryService
{
    protected function getIndexConfigKey(): string
    {
        return 'elasticsearch.authors_index';
    }
}
