<?php

declare(strict_types=1);

namespace App\Services\Book;

use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Services\Abstracts\AbstractSearchSuggestService;

class SearchSuggestCatalogBookService extends AbstractSearchSuggestService
{
    public function __construct(
        BookRepositoryInterface $repository,
        SearchBookByQueryService $searchService,
    ) {
        parent::__construct($repository, $searchService);
    }

    protected function mapResult(mixed $entity): array
    {
        return [
            'id' => $entity->id,
            'title' => $entity->title,
            'author' => $entity->authorName,
            'cover_image' => $entity->coverImage,
            'price' => $entity->price,
            'url' => route('catalog.show', $entity->slug),
        ];
    }

    protected function fetchEntities(array $ids): array
    {
        return $this->repository->getOrderedByIds($ids, perPage: 5)->items;
    }
}
