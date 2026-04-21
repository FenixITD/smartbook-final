<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\PaginatedResponseDto;
use App\Dto\Review\ReviewDto;
use App\Dto\Review\ReviewFiltersDto;
use App\Dto\Review\ReviewResponseDto;
use App\Models\Review;
use App\Repositories\Interfaces\ReviewRepositoryInterface;

final class ReviewRepository implements ReviewRepositoryInterface
{
    /** @return array<ReviewResponseDto> */
    public function getList(ReviewFiltersDto $filters): array
    {
        return Review::query()
            ->when($filters->id !== null, static fn ($q) => $q->where('id', $filters->id))
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage)
            ->getCollection()
            ->map(static fn (Review $review) => ReviewResponseDto::fromModel($review))
            ->all();
    }

    public function getWebList(ReviewFiltersDto $filters): PaginatedResponseDto
    {
        $paginator = Review::query()
            ->when($filters->id !== null, static fn ($q) => $q->where('id', $filters->id))
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage);

        return PaginatedResponseDto::fromPaginator($paginator);
    }

    /** @return array<mixed> */
    public function all(): array
    {
        return Review::orderBy('id')->get()->all();
    }

    public function getById(int $id): ReviewResponseDto|null
    {
        $review = Review::find($id);

        return $review !== null ? ReviewResponseDto::fromModel($review) : null;
    }

    public function create(ReviewDto $data): ReviewResponseDto
    {
        /** @var Review $review */
        $review = Review::create($data->toArray());

        return ReviewResponseDto::fromModel($review);
    }

    public function update(int $id, ReviewDto $data): ReviewResponseDto|null
    {
        $review = Review::findOrFail($id);

        $review->update($data->toArray());

        /** @var Review $fresh */
        $fresh = $review->fresh();

        return ReviewResponseDto::fromModel($fresh);
    }

    public function delete(int $id): bool
    {
        return (bool) Review::destroy($id);
    }
}
