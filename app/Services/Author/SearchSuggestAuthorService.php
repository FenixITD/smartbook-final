<?php

declare(strict_types=1);

namespace App\Services\Author;

use App\Dto\Author\AuthorResponseDto;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use App\Services\Abstracts\AbstractSearchSuggestService;

class SearchSuggestAuthorService extends AbstractSearchSuggestService
{
    public function __construct(
        AuthorRepositoryInterface $repository,
        SearchAuthorByQueryService $searchService,
    ) {
        parent::__construct($repository, $searchService);
    }

    /**
     * @param AuthorResponseDto $entity
     * @return array{id: int, name: string, url: string}
     */
    protected function mapResult(mixed $entity): array
    {
        return [
            'id' => $entity->id,
            'name' => $entity->name,
            'url' => route('authors.show', $entity->id),
        ];
    }
}
