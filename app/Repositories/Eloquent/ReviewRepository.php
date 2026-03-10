<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTO\Review\ReviewFiltersDTO;
use App\DTO\Review\ReviewResponseDTO;
use App\Models\Review;
use App\Repositories\Interfaces\ReviewRepositoryInterface;

final class ReviewRepository implements ReviewRepositoryInterface
{
    public function getList(ReviewFiltersDTO $filters): array
    {
        $query = Review::query()
            ->when($filters->search !== null, fn ($q) => $q->where('id', 'like', "%{$filters->search}%"));

        $paginator = $query->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($filters->perPage);

        return $paginator->getCollection()
            ->map(fn (Review $review) => ReviewResponseDTO::fromModel($review))->all();
    }

    public function getById(int $id): ?ReviewResponseDTO
    {
        $review = Review::find($id);

        return $review ? ReviewResponseDTO::fromModel($review) : null;
    }

    public function create(array $data): ReviewResponseDTO
    {
        $review = Review::create($data);

        return ReviewResponseDTO::fromModel($review);
    }

    public function update(Review $review, array $data): ?ReviewResponseDTO
    {
        $review->update($data);

        return ReviewResponseDTO::fromModel($review->fresh());
    }

    public function delete(Review $review): bool
    {
        return $review->delete();
    }
}
