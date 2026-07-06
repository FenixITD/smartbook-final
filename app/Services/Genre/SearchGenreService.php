<?php

declare(strict_types=1);

namespace App\Services\Genre;

use App\Services\Abstracts\AbstractSearchService;

class SearchGenreService extends AbstractSearchService
{
    protected function getIndexConfigKey(): string
    {
        return 'elasticsearch.genres_index';
    }
}
