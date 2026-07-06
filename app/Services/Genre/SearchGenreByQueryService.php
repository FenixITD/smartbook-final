<?php

declare(strict_types=1);

namespace App\Services\Genre;

use App\Services\Abstracts\AbstractSearchByQueryService;

class SearchGenreByQueryService extends AbstractSearchByQueryService
{
    protected function getIndexConfigKey(): string
    {
        return 'elasticsearch.genres_index';
    }
}
