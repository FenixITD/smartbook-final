<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Dto\PaginatedResponseDto;
use App\Dto\Review\BookReviewResponseDto;
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

    public function getById(int $id): ReviewResponseDto|null
    {
        $reviewId = Review::find($id);

        return $reviewId !== null ? ReviewResponseDto::fromModel($reviewId) : null;
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
        return (bool) Review::findOrFail($id)->delete();
    }

    public function getByBookId(int $bookId, int $perPage = 10): PaginatedResponseDto
    {
        $paginator = Review::with('user')
            ->where('book_id', $bookId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $items = $paginator->getCollection()
            ->map(static fn (Review $review) => BookReviewResponseDto::fromModel($review))
            ->all();

        return new PaginatedResponseDto(
            items: $items,
            total: $paginator->total(),
            perPage: $paginator->perPage(),
            currentPage: $paginator->currentPage(),
            lastPage: $paginator->lastPage(),
            links: $paginator->links()->toHtml(),
        );
    }
}
