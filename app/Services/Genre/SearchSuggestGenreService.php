<?php

declare(strict_types=1);

namespace App\Services\Genre;

use App\Dto\Genre\GenreResponseDto;
use App\Repositories\Interfaces\GenreRepositoryInterface;

class SearchSuggestGenreService
{
    public function __construct(
        private GenreRepositoryInterface $repository,
        private SearchGenreByQueryService $searchService,
    ) {
    }

    /**
     * @return array<int, array{id: int, name: string, url: string}>
     */
    public function execute(string $query): array
    {
        $ids = $this->searchService->search($query, limit: 5);

        if ($ids === []) {
            return [];
        }

        return array_values(array_map(
            static fn (GenreResponseDto $genre): array => [
                'id' => $genre->id,
                'name' => $genre->name,
                'url' => route('genres.show', $genre->id),
            ],
            $this->repository->getByIds($ids),
        ));
    }
}
