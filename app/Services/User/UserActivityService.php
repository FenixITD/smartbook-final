<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Dto\ActivityLog\ActivityLogFiltersDto;
use App\Dto\ActivityLog\ActivityLogResponseDto;
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

        $bookIds = [];

        /** @var ActivityLogResponseDto $log */
        foreach ($logs->items as $log) {
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

        $bookIds = array_values(array_unique($bookIds));

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
