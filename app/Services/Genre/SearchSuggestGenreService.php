<?php

declare(strict_types=1);

namespace App\Services\Genre;

use App\Dto\Genre\GenreResponseDto;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use App\Services\Abstracts\AbstractSearchSuggestService;

/**
 * @extends AbstractSearchSuggestService<GenreResponseDto>
 */
class SearchSuggestGenreService extends AbstractSearchSuggestService
{
    public function __construct(
        GenreRepositoryInterface $repository,
        SearchGenreByQueryService $searchService,
    ) {
        parent::__construct($repository, $searchService);
    }

    /**
     * @param mixed $entity
     * @return array<string, mixed>
     */
    protected function mapResult(mixed $entity): array
    {
        /** @var GenreResponseDto $entity */
        return [
            'id' => $entity->id,
            'name' => $entity->name,
            'url' => route('genres.show', $entity->id),
        ];
    }
}
