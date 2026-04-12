<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\PaginatedResponseDto;
use App\Dto\Review\ReviewDto;
use App\DTO\Review\ReviewFiltersDto;
use App\DTO\Review\ReviewResponseDto;
use App\Models\Review;
use App\Repositories\Interfaces\ReviewRepositoryInterface;

final class ReviewRepository implements ReviewRepositoryInterface
{
    public function getList(ReviewFiltersDto $filters): array
    {
        return Review::query()
            ->when($filters->search !== null, fn ($q) => $q->where('id', 'like', "%{$filters->search}%"))
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->getCollection()
            ->map(fn (Review $review) => ReviewResponseDto::fromModel($review))
            ->all();
    }

    public function getWebList(ReviewFiltersDto $filters): PaginatedResponseDto
    {
        $paginator = Review::query()
            ->when($filters->search !== null, fn ($q) => $q->where('id', 'like', "%{$filters->search}%"))
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage);

        return PaginatedResponseDto::fromPaginator($paginator);
    }

    public function all(): array
    {
        return Review::orderBy('id')->get()->all();
    }

    public function getById(int $id): ?ReviewResponseDto
    {
        $review = Review::find($id);

        return $review ? ReviewResponseDto::fromModel($review) : null;
    }

    public function create(ReviewDto $data): ReviewResponseDto
    {
        $review = Review::create($data->toArray());

        return ReviewResponseDto::fromModel($review);
    }

    public function update(int $id, ReviewDto $data): ?ReviewResponseDto
    {
        $review = Review::findOrFail($id);

        $review->update($data->toArray());

        return ReviewResponseDto::fromModel($review->fresh());
    }

    public function delete(int $id): bool
    {
        return (bool) Review::destroy($id);
    }
}
