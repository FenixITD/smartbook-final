<?php

declare(strict_types=1);

namespace App\Services\Abstracts;

abstract class AbstractSearchSuggestService
{
    public function __construct(
        protected mixed $repository,
        protected mixed $searchService,
    ) {
    }

    abstract protected function mapResult(mixed $entity): array;

    public function execute(string $query): array
    {
        $ids = $this->searchService->search($query, limit: 5);

        if ($ids === []) {
            return [];
        }

        return array_values(array_map(
            fn (mixed $entity): array => $this->mapResult($entity),
            $this->fetchEntities($ids)
        ));
    }

    protected function fetchEntities(array $ids): array
    {
        return $this->repository->getByIds($ids);
    }
}
