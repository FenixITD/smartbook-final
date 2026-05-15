<?php

declare(strict_types=1);

namespace App\Services\UserActivity;

use App\Dto\ActivityLog\ActivityLogFiltersDto;
use App\Dto\Book\BookResponseDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Interfaces\ActivityLogRepositoryInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;

final readonly class UserActivityService
{
    public function __construct(
        private ActivityLogRepositoryInterface $activityRepository,
        private BookRepositoryInterface $bookRepository,
    ) {
    }

    /**
     * @return array{0: PaginatedResponseDto, 1: array<int, BookResponseDto>}
     */
    public function getWithBooks(ActivityLogFiltersDto $filters): array
    {
        $logs = $this->activityRepository->getPaginated($filters);

        $bookIds = array_values(array_unique(array_filter(
            array_map(static fn ($log) =>
                $log->properties['book_id']
                ?? $log->properties['attributes']['book_id']
                ?? $log->properties['old']['book_id']
                ?? null,
                $logs->items
            ),
        )));

        if ($bookIds === []) {
            return [$logs, []];
        }

        $books = $this->bookRepository->findByIdsWithAuthor($bookIds);

        /** @var array<int, BookResponseDto> $booksById */
        $booksById = collect($books)
            ->keyBy(static fn (BookResponseDto $b) => $b->id)
            ->all();

        return [$logs, $booksById];
    }
}
