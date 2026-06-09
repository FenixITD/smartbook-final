<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Dto\ActivityLog\ActivityLogFiltersDto;
use App\Dto\ActivityLog\ActivityLogResponseDto;
use App\Dto\Book\BookResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Interfaces\ActivityLogRepositoryInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;

class UserActivityService
{
    public function __construct(
        private ActivityLogRepositoryInterface $activityRepository,
        private BookRepositoryInterface $bookRepository,
    ) {
    }

    /**
     * @return array{0: PaginatedResponseDto, 1: array<int, BookResponseDto>}
     */
    public function fetchWithBooks(ActivityLogFiltersDto $filters): array
    {
        $logs = $this->activityRepository->getPaginated($filters);

        $bookIds = $this->extractBookIds($logs->items);

        if ($bookIds === []) {
            return [$logs, []];
        }

        $books = $this->bookRepository->findByIdsWithAuthor($bookIds);

        /** @var array<int, BookResponseDto> $booksById */
        $booksById = collect($books)
            ->keyBy(static fn (BookResponseDto $bookResponseDto) => $bookResponseDto->id)
            ->all();

        return [$logs, $booksById];
    }

    /**
     * @param array<mixed> $items
     * @return array<int>
     */
    private function extractBookIds(array $items): array
    {
        $bookIds = [];

        foreach ($items as $log) {
            if (!$log instanceof ActivityLogResponseDto) {
                continue;
            }

            $propsArray = $log->properties;

            /** @var array<string, mixed>|null $attributes */
            $attributes = $propsArray['attributes'] ?? null;

            /** @var array<string, mixed>|null $old */
            $old = $propsArray['old'] ?? null;

            $bookId = $propsArray['book_id']
                ?? (is_array($attributes) ? ($attributes['book_id'] ?? null) : null)
                ?? (is_array($old) ? ($old['book_id'] ?? null) : null)
                ?? null;

            if ($bookId !== null && is_scalar($bookId)) {
                $bookIds[] = (int) $bookId;
            }
        }

        return array_values(array_unique($bookIds));
    }
}
