<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Dto\Book\BookResponseDto;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Services\Abstracts\AbstractSearchSuggestService;

/**
 * @extends AbstractSearchSuggestService<BookResponseDto>
 */
class SearchSuggestBookService extends AbstractSearchSuggestService
{
    public function __construct(
        BookRepositoryInterface $repository,
        SearchBookByQueryService $searchService,
    ) {
        parent::__construct($repository, $searchService);
    }

    protected function mapResult(mixed $entity): array
    {
        /** @var BookResponseDto $entity */
        return [
            'id' => $entity->id,
            'title' => $entity->title,
            'author' => $entity->authorName,
            'url' => route('books.show', $entity->id),
        ];
    }

    /**
     * @param array<int> $ids
     * @return array<int, BookResponseDto>
     */
    protected function fetchEntities(array $ids): array
    {
        /** @phpstan-ignore-next-line */
        return $this->repository->getOrderedByIds($ids, perPage: 5)->items;
    }
}
