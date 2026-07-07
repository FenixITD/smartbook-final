<?php

declare(strict_types=1);

namespace App\Services\Abstracts;

/**
 * @template TEntity
 */
abstract class AbstractSearchSuggestService
{
    public function __construct(
        protected mixed $repository,
        protected AbstractSearchByQueryService $searchService,
    ) {
    }

    /**
     * @param mixed $entity
     * @return array<string, mixed>
     */
    abstract protected function mapResult(mixed $entity): array;

    /**
     * @return array<int, array<string, mixed>>
     */
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

    /**
     * @param array<int> $ids
     * @return array<int, TEntity>
     */
    protected function fetchEntities(array $ids): array
    {
        /** @phpstan-ignore-next-line */
        return $this->repository->getByIds($ids);
    }
}
