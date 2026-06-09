<?php

declare(strict_types=1);

namespace App\Services\Author;

use App\Dto\Author\AuthorResponseDto;
use App\Repositories\Interfaces\AuthorRepositoryInterface;

class SearchSuggestAuthorService
{
    public function __construct(
        private AuthorRepositoryInterface $repository,
        private SearchAuthorByQueryService $searchService,
    ) {
    }

    /**
     * @return array<int, array{id: int, name: string, url: string}>
     *
     * Fetches up to 5 author suggestions for autocomplete search, returning basic author details and their URL
     */
    public function execute(string $query): array
    {
        $ids = $this->searchService->search($query, limit: 5);

        if ($ids === []) {
            return [];
        }

        return array_values(array_map(
            static fn (AuthorResponseDto $author): array => [
                'id' => $author->id,
                'name' => $author->name,
                'url' => route('authors.show', $author->id),
            ],
            $this->repository->getByIds($ids),
        ));
    }
}
