<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\Dashboard\DashboardFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Models\Book;
use App\Repositories\Interfaces\DashboardRepositoryInterface;

final class DashboardRepository implements DashboardRepositoryInterface
{
    public function getDashboardList(DashboardFiltersDto $filters): PaginatedResponseDto
    {
        [$column, $direction] = match ($filters->sort) {
            'price_asc' => ['price', 'asc'],
            'price_desc' => ['price', 'desc'],
            'newest' => ['publish_year', 'desc'],
            default => ['average_rating', 'desc'],
        };

        $paginator = Book::with(['author', 'genres'])
            ->when($filters->search, fn ($q) => $q->where('title', 'like', "%{$filters->search}%"))
            ->when($filters->genre, fn ($q) => $q->whereHas('genres', fn ($q) => $q->where('genres.id', $filters->genre)))
            ->when($filters->author, fn ($q) => $q->where('author_id', $filters->author))
            ->when($filters->year, fn ($q) => $q->where('publish_year', $filters->year))
            ->when($filters->status, fn ($q) => $q->where('status', $filters->status))
            ->orderBy($column, $direction)
            ->paginate($filters->perPage)
            ->withQueryString();

        return PaginatedResponseDto::fromPaginator($paginator);
    }
}
