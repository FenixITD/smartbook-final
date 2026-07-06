<?php

declare(strict_types=1);

namespace App\Services\Genre;

use App\Dto\Genre\GenreResponseDto;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use App\Services\Abstracts\AbstractSearchSuggestService;

class SearchSuggestGenreService extends AbstractSearchSuggestService
{
    public function __construct(
        GenreRepositoryInterface $repository,
        SearchGenreByQueryService $searchService,
    ) {
        parent::__construct($repository, $searchService);
    }

    /**
     * @param GenreResponseDto $entity
     * @return array{id: int, name: string, url: string}
     */
    protected function mapResult(mixed $entity): array
    {
        return [
            'id' => $entity->id,
            'name' => $entity->name,
            'url' => route('genres.show', $entity->id),
        ];
    }
}
